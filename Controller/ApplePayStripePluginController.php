<?php

/*
 * This file is part of the ApplePayStripePlugin
 *
 * Copyright (C) 2019 binaryquaver
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ApplePayStripePlugin\Controller;

require_once(__DIR__."/../vendor/autoload.php");

use Eccube\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use \Stripe\Stripe;
use Plugin\ApplePayStripePlugin\Entity\OrderStripeCharge;

class ApplePayStripePluginController
{

    /**
     * ApplePayStripePlugin画面
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function payment(Application $app, Request $request)
    {
        // add code...
        if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
            $data = json_decode($request->getContent(), true);
        }
        $config = $app['orm.em']->getRepository('Plugin\ApplePayStripePlugin\Entity\ApplePayStripePluginConfig')->findOneBy(array('id' => 1));

        \Stripe\Stripe::setApiKey($config->getApiKeySecret());
        $charge = \Stripe\Charge::create(['amount' => $data['amount'], 'currency' => 'jpy', 'source' => $data['stripe_token'], 'description' => "order_no: " . $data['orderno']]);
        if (!is_null($charge)) {

            $osc = new OrderStripeCharge();
            $osc->setId($data['orderno']);
            $osc->setStripeChargeId($charge->id);
            $app['orm.em']->persist($osc);
            $app['orm.em']->flush($osc);

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
