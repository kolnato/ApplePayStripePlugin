<?php

/*
 * This file is part of the ApplePayStripePlugin
 *
 * Copyright (C) 2019 binaryquaver
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ApplePayStripePlugin;

use Eccube\Application;
use Eccube\Event\EventArgs;
use Eccube\Event\TemplateEvent;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;

class ApplePayStripePluginEvent
{

    /** @var  \Eccube\Application $app */
    private $app;

    private $config = null;

    private $willBePaidByApplePay = false;

    /**
     * ApplePayStripePluginEvent constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onAppRequest(GetResponseEvent $event)
    {
    }

    /**
     * @param EventArgs $event
     */
    public function onFrontCartUpInitialize(EventArgs $event)
    {
    }

    /**
     * @param TemplateEvent $event
     */
    public function onRenderShoppingIndex(TemplateEvent $event)
    {
        if (!$this->willBePaidByApplePay || is_null($this->config)) {
            return;
        }
        $src = $event->getSource();

        $app = $this->app;
        $Order = $app['eccube.service.shopping']->getOrder($app['config']['order_processing']);

        $stripe_api_key = $this->config->getApiKey();
        $payment_label = $app['eccube.repository.base_info']->get()->getShopName();
        $payment_amount = intval($Order["payment_total"]);
        $payment_email = $Order["email"];
        $payment_name = $Order["name01"] . " " . $Order["name02"];
        $order_no = $Order["id"];
        $order_button_placeholder = $this->config->getOrderButtonPlaceholder();

        $parameters = $event->getParameters();

        $button = <<<EOS
<script src="https://js.stripe.com/v3/"></script>
<script>
$(function() {
  const html = "<style>@supports (-webkit-appearance: -apple-pay-button) {.apple-pay-button {display: inline-block;-webkit-appearance: -apple-pay-button;}.apple-pay-button-black {-apple-pay-button-style: black;}}@supports not (-webkit-appearance: -apple-pay-button) {.apple-pay-button {display: inline-block;background-size: 100% 60%;background-repeat: no-repeat;background-position: 50% 50%;border-radius: 5px;padding: 0px;box-sizing: border-box;min-width: 200px;min-height: 32px;max-height: 64px;}.apple-pay-button-black {background-image: -webkit-named-image(apple-pay-logo-white);background-color: black;}}</style><div id=\"apple-pay-button\" class=\"apple-pay-button apple-pay-button-black\"></div>";
  $("#{$order_button_placeholder}").before(html);
  $("#apple-pay-button").css("width", "100%");
  $("#{$order_button_placeholder}").hide();

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

});
</script>
EOS;

        $pos = strpos($src, "{% endblock javascript %}");
        if ($pos) {
            $newsrc = substr($src, 0, $pos) . $button . substr($src, $pos);
            $event->setSource($newsrc);
        }
    }

    /**
     * @param EventArgs $event
     */
    private function initForEventArgs(EventArgs $event)
    {
        $this->config = $this->app['orm.em']->getRepository('Plugin\ApplePayStripePlugin\Entity\ApplePayStripePluginConfig')->findOneBy(array('id' => 1));

        if ($event->hasArgument('Order')) {
            $this->willBePaidByApplePay = $event->getArgument('Order')->getPayment()->getMethod() == 'Apple Pay';
        }
    }

    /**
     * @param EventArgs $event
     */
    public function onFrontShoppingIndexInitialize(EventArgs $event)
    {
        $this->initForEventArgs($event);
    }

    /**
     * @param EventArgs $event
     */
    public function onFrontShoppingConfirmInitialize(EventArgs $event)
    {
        $this->initForEventArgs($event);
    }
}
