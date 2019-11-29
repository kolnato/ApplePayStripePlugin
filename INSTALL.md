# ApplePayStripePluginインストール

by hironobu koura <kolnato.owelek@gmail.com>

## 準備

### Stripeアカウントの作成(未登録の場合)

https://dashboard.stripe.com/register からアカウントを登録します。登録内容についてはstripeの案内にしたがって入力します。

アカウント登録完了したのち、サインイン後の画面で「本番環境利用の申請」というメニューがあるので、それを選択して所定の入力項目を埋めて提出します。内容に問題がなければ本番申請が通るまで一時間もかかりません。

### サイトのHTTPS対応

インストール先のECCUBEサイトのURLが https://〜 で始まることを確認しておきます。この時、HTTPS未対応で https://〜 で始まるURLでアクセスできない場合はこの先の「ECサイトの登録」が行えませんのでご注意ください。

### StripeアカウントにECサイトのURLを登録する

https://dashboard.stripe.com/account/apple_pay において、対象となるサイトのURLを登録します。

「+ 新しいドメインを追加」ボタンを押すと「新しいドメインを追加」ダイアログが表示されるので、書かれた内容に従います。

* ファイルをホストするドメインを指定する

当該サイトの「ドメイン部分」を入力します。例えば https://store.example.com/〜 で始まるURLが今回の対象ならば「store.example.com」を、 https://www.example-store.co.jp/〜 で始まるならば「www.example-store.co.jp」を入力します。

* 「確認ファイルをダウンロード...」ボタンを押す

ここで「apple-developer-merchantid-domain-association」という名前のファイルがダウンロードされるので保持しておきます。

* 確認ファイルをホストする

前項で保持した確認ファイルを、所定のURLからダウンロードできる場所に設置します。これはStripeから指定があって、下記のような形式のURLになるよう決められています。

```
https://(「ファイルをホストするドメインを指定する」で設定したドメイン)/.well-known/apple-developer-merchantid-domain-association
```

つまり、「ファイルをホストするドメインを指定する」で入力したドメインが仮に「store.example.com」だった場合は、

```
https://store.example.com/.well-known/apple-developer-merchantid-domain-association
```

のURLで開ける位置に前項でダウンロードした確認ファイルを設置する必要があるということになります。

これはそれぞれのWEBサーバの設定により異なりますので実際に設定内容を確認しながら進めていただく必要がありますが、下記のような流れで進められればおおよそのケースで対応可能です。

1. 「https://(「ファイルをホストするドメインを指定する」で設定したドメイン)/」でダウンロードされるHTMLファイル(多くの場合index.html)がどこにあるかを把握する
2. そのindex.htmlファイルと同じディレクトリ階層に、「.well-known」ディレクトリを作成する
3. 2.で作成した .well-known ディレクトリに apple-developer-merchantid-domain-association を置く

以上完了後、`https://(「ファイルをホストするドメインを指定する」で設定したドメイン)/.well-known/apple-developer-merchantid-domain-association` のURLをブラウザのアドレスバーに入力するなどしてファイルにアクセスできることを確認したら設置完了です。「新しいドメインを追加」の「追加」ボタンを押して、ドメインの追加を完了してください。

## 手順

### ECCUBE環境にプラグインのインストール

ApplePayStripePluginの最新パッケージを取得します。バージョンや取得方法については適宜作者に確認してください。

インストールはECCUBEの管理画面にログインして行います。画面左のメインメニューから「オーナーズストア」→「プラグイン」→「プラグイン一覧」の順で選択して表示される「プラグインのアップロードはこちら」のボタンを押します。

ここで「新規プラグインアップロード」画面に遷移するので、「ファイルを選択」ボタンを押して先述のApplePayStripePluginの最新パッケージファイル(ZIPファイル)を選択して「アップロード」ボタンを押してアップロードします。

アップロード完了後、「プラグイン一覧」の画面に戻るので、そこで「ApplePayStripePlugin」の項目で「有効にする」リンクを押し、インストールを完了させます。

### APIキーの入力

準備段階で用意したStripeアカウントから、APIキーを取得して設定します。

https://dashboard.stripe.com/apikeys から、本番用の「公開可能キー」と「シークレットキー」の2種類のAPIキーを取得します。

ECCUBEの管理画面に戻り、「ApplePay＋Stripe管理」のメニューを選択して「APIキー」をクリックします。APIキーを入力する画面に遷移するので、ここでそれぞれ「公開キー」に「公開可能キー」を、「秘密キー」に「シークレットキー」を入力し、「APIキーを登録」ボタンを押します。

### 「支払い」ボタンのHTML id指定

続いて、ApplePay決済を行うときに必要なApplePay専用支払いボタンを設置します。ボタンの位置を識別するため当該ボタンのHTML上の記述を確認し、IDに何が指定されているかを確認します。これを「ご注文内容のご確認」(https://store.example.com/shopping)ページの「注文する」ボタンに結び付けて設置することになります。

```
<dl id="summary_box__shipping_price">
    <dt>送料</dt>
    <dd>¥ 1,000</dd>
</dl>
                        <div id="summary_box__result" class="total_amount">
    <p id="summary_box__total_amount" class="total_price">合計 <strong class="text-primary">¥ 4,024<span class="small">税込</span></strong></p>
    <p id="summary_box__confirm_button"><button id="order-button" type="submit" class="btn btn-primary btn-block prevention-btn prevention-mask">注文する</button></p>
</div>
```

これはデフォルトのテンプレートを使ったときの、「注文する」ボタンのHTMLです。`<button id="order-button" `とあるので、この「order-button」を記録しておきます。管理画面に戻って「ApplePay+Stripe管理」→「APIキー」のメニューから、「ApplePayボタン設置位置」の項目に上記の「order-button」を入力します(デフォルトもorder-buttonなので同じならば入力の必要なし)。登録ボタンを押して設定を完了します。

### 配送手段設定

最後にプラグインのインストールによって設定された新規の支払い方法である「Apple Pay」を、実際の購入時に使用できるよう「配送方法」と結び付ける必要があります。管理画面の左メニューから「設定」→「基本情報設定」→「配送方法設定」メニューを選択し、配送方法一覧ページを表示します。そこに現れる配送方法項目を一件ずつ選択します(デフォルトなら「サンプル宅配 / サンプル宅配」と「サンプル業者 / サンプル業者」の2件)

選択するとそれぞれの「配送先管理」ページに遷移するので、「支払方法設定」の項目に「Apple Pay」の項目が現れているのを確認したらチェックボックスをONにして、「登録」ボタンを押します。

必要な配送方法全てに上記のチェックを入れて登録を完了したら、設定の完了です。サイト上で実際にカートを操作し「Apple Pay」を選択して購入できることを確認しましょう。
