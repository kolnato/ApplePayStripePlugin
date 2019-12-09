# About ApplePayStripePlugin

by kolnato.owelek@gmail.com

## 概要

本ドキュメントは、当プラグインの作成とプラグインの動作の概略を順を追って説明したものです。詳細については下記のドキュメント等を参考にでき内容が分かる読者を前提にしておりますので、ある程度省略しています。これらの資料を並行して確認しながら進めてください。

* ECCUBEプラグインチュートリアル
https://doc.ec-cube.net/plugin_tutorial
* Accept a Card Payment | Stripe Payment
https://stripe.com/docs/payments/accept-a-payment-charges
* Stripe API Reference
https://stripe.com/docs/api
* Apple Pay on the Web | Apple Developer Documentation
https://developer.apple.com/documentation/apple_pay_on_the_web
* Apple Pay | Stripe payments
https://stripe.com/docs/apple-pay

またインストールに関しての情報は INSTALL.md にまとめています。そちらも合わせてご参照ください。

## 処理の流れ

購入・決済時の処理の流れを整理すると下記のようになる。

- ブラウザ: 決済手段を選んでApplePayの決済ボタンを押す。
- Stripe&ApplePay: 端末上での本人認証やカード会社への問い合わせを行い、決済認証可否を判断する。
- ブラウザ: 決済認証可否がApplePayのサーバから返される。可ならばStripeトークンがブラウザに付与される。
- ブラウザ: 上記StripeトークンとECCUBE注文情報を紐づけるため、その仲介処理を行うプラグインAPIを呼び出す。
- プラグイン: ECCUBE注文IDとStripeトークンを受け取ってStripe Charge APIを呼び出し、決済処理の完了を通知する。
- Stripe: Charge APIの呼び出しを受け取って処理する。
- プラグイン: APIの返値をブラウザに通知する。
- ブラウザ: APIが成功したのを確認したら、決済完了ページに遷移する。この決済完了ページへの遷移を持って、ECCUBEも本体側の決済処理を連携して完了する。

## 開発

前項を受けて、当プラグインの構成要素をまとめると下記のように整理できる。

- 画面内にApplePayボタンを表示
- ApplePayとStripeに対して決済処理開始を通知
- (上記通知結果を受けて)ECCUBEの注文情報とStripeトークンを紐づける

これ以外の部分はECCUBE本体やApplePay、Stripeが行うことになる。

### ApplePayボタンの表示

決済時、ApplePayを選択した時にApplePay用の「支払うボタン」を表示してApplePayの決済処理に遷移するよう誘導しなければならない。
そのためまず下記のCSSを決済ページに適用しておく。

```
@supports (-webkit-appearance: -apple-pay-button) {
  .apple-pay-button {
    display: inline-block;-webkit-appearance: -apple-pay-button;
  }
  .apple-pay-button-black {
    -apple-pay-button-style: black;
  }
}
@supports not (-webkit-appearance: -apple-pay-button) {
  .apple-pay-button {
    display: inline-block;
    background-size: 100% 60%;
    background-repeat: no-repeat;
    background-position: 50% 50%;
    border-radius: 5px;
    padding: 0px;
    box-sizing: border-box;
    min-width: 200px;
    min-height: 32px;
    max-height: 64px;
  }
  .apple-pay-button-black {
    background-image: -webkit-named-image(apple-pay-logo-white);
    background-color: black;
  }
}
```

次に、下記のようなJavaScriptコードがApplePay選択時に有効になるよう挿入しておく。

```
$(function() {
  // 下記のjsは各サイトのカスタマイズ状況によって異なるため置き換え前提の仮のコードです。
  // あくまで参考としてご覧ください。
  if ($("input[name='shopping[payment]']:checked").val() == 1) {  
    const html= "<div id=\"apple-pay-button\" class=\"apple-pay-button apple-pay-button-black\"></div>";
    $("#{$order_button_placeholder}").before(html);
    $("#apple-pay-button").css("width", "100%");
    $("#{$order_button_placeholder}").hide();
  }
});
```

### ApplePayとStripeに対して決済処理開始を通知

決済時に行われるApplePayのサーバとの通信部分のJSコードをページに埋め込む。
前項で作成した`div#apple-pay-button`のclickイベントをJavaScriptに渡すためにPayment Request ButtonというStripeのコンポーネントを使用する。

```
  const stripe = Stripe("{$stripe_api_key}");

  const paymentRequest = stripe.paymentRequest({
    country: 'JP',
    currency: 'jpy',
    total: {
      label: '{$payment_label}',
      amount: {$payment_amount}
    },
    requestPayerName: true,
    requestPayerEmail: true,
  });

  const elements = stripe.elements();
  const prButton = elements.create('paymentRequestButton', {
    paymentRequest: paymentRequest,
  });

  paymentRequest.canMakePayment().then(function(result) {
    if (result) {
      prButton.mount('#apple-pay-button');
    } else {
      alert('エラー: ApplePay の設定を確認してください');
      document.getElementById('apple-pay-button').style.display = 'none';
    }
  }).catch(function(error) {
    console.log(error);
  });
```

`prButton.mount('#apple-pay-button')`によって div#apple-pay-button のclickイベントに対するイベントハンドラとしてコールバックされ、すなわちここから決済処理を起動する役割を持つ。

その後、ApplePayおよびStripeの処理が通り認証OKが出ると、上記のPayment RequestからStripeトークンが生成されてくる。これが`token`イベントとして起動するので、イベントハンドラを用意しておく必要がある。

```
  paymentRequest.on('token', async (ev) => {
    const response = await fetch('/plugin/applepaystripeplugin/payment', {
      method: 'POST',
      body: JSON.stringify({
        stripe_token: ev.token.id,
        amount: {$payment_amount},
        email: "{$payment_email}",
        name: "{$payment_name}",
        orderno: "{$order_no}"
      }),
      headers: {'content-type': 'application/json'},
    });

    if (response.ok) {
      ev.complete('success');
      $("#{$order_button_placeholder}").click();
    } else {
      ev.complete('fail');
    }
  });
```

`await fetch('/plugin/applepaystripeplugin/payment', ...)`は、自分のECCUBEサイトにインストールされた当プラグイン(ApplePayStripePlugin)のAPIコントローラを呼び出す処理になる。次項がその内容についてのものとなる。

このプラグインAPIの応答がOKならばECCUBEの決済処理全体がOKとなる。


### ECCUBEの注文情報とStripeトークンを紐づける

前項で生成されたStripeトークンを受けて、今回の注文情報をECCUBEとStripeの間で結びつける役割を持つのがこのプラグインAPIコントローラである。
プラグイン内のAPIコントローラとして実装されているので、前項ではこれをJS fetch()を用いて呼び出した。

```
class ApplePayStripePluginController
{
    // ...
    
    public function payment(Application $app, Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $config = $app['orm.em']->getRepository('Plugin\ApplePayStripePlugin\Entity\ApplePayStripePluginConfig')->findOneBy(array('id' => 1));

        \Stripe\Stripe::setApiKey("(Stripe APIシークレットキー)");
        $charge = \Stripe\Charge::create(['amount' => $data['amount'], 'currency' => 'jpy', 'source' => $data['stripe_token'], 'description' => "order_no: " . $data['orderno']]);
        if (!is_null($charge)) {
            // ...
            
            return new Response(
                '{"status":"OK","charge-id":"'.$charge->id.'"}',
                Response::HTTP_OK,
                array('Content-Type' => 'application/json; charset=utf-8')
            );
        } else {
            return new Response(
                '{"status":"ERROR"}',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                array('Content-Type' => 'application/json; charset=utf-8')
            );
        }
    }

}
```

`\Stripe\Charge::create()`というAPIを用いてStripeに通知し、この課金と決済処理を最終的に完了させる。

この時ブラウザから金額やECCUBE注文IDなども付与し、StripeとECCUBEの間で情報の紐付けが行われているのが確認できる。
