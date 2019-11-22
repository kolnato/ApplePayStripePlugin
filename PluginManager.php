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
use Eccube\Entity\PaymentOption;
use Eccube\Plugin\AbstractPluginManager;

class PluginManager extends AbstractPluginManager
{
    const PAYMENT_METHOD = "Apple Pay";

    /**
     * プラグインインストール時の処理
     *
     * @param $config
     * @param Application $app
     * @throws \Exception
     */
    public function install($config, Application $app)
    {
        $this->migrationSchema($app, __DIR__ . '/Resource/doctrine/migration', $config['code']);
    }

    /**
     * プラグイン削除時の処理
     *
     * @param $config
     * @param Application $app
     */
    public function uninstall($config, Application $app)
    {
        $this->migrationSchema($app, __DIR__ . '/Resource/doctrine/migration', $config['code'], 0);
    }

    /**
     * プラグイン有効時の処理
     *
     * @param $config
     * @param Application $app
     * @throws \Exception
     */
    public function enable($config, Application $app)
    {
        $payment = $app['eccube.repository.payment']->findOneBy(['method' => self::PAYMENT_METHOD]);
        if (is_null($payment)) {
            $softDeleteFilter = $app['orm.em']->getFilters()->getFilter('soft_delete');
            $softDeleteFilter->setExcludes(['Eccube\Entity\Member']);
            $defaultCreator = $app['eccube.repository.member']->findOneBy(['id' => 1]);
            $payment = $app['eccube.repository.payment']->findOrCreate(0);
            $payment->setMethod(self::PAYMENT_METHOD);
            $payment->setCharge(0);
            $payment->setRuleMin(0);
            $payment->setCreator($defaultCreator);
            $app['orm.em']->persist($payment);
            $app['orm.em']->flush($payment);
        }

	/*
        $deliveries = $app['eccube.repository.delivery']->findAll();
        foreach ($deliveries as $delivery) {
            $paymentOption = $app['eccube.repository.payment_option']->findOneBy(['Payment' => $payment, 'Delivery' => $delivery]);
            if (is_null($paymentOption)) {
                $paymentOption = new PaymentOption();
                $paymentOption->setDelivery($delivery);
                $paymentOption->setPayment($payment);
                $paymentOption->setDeliveryId($delivery->getId());
                $paymentOption->setPaymentId($payment->getId());
                $app['orm.em']->persist($paymentOption);
            }
        }
	 */

        $app['orm.em']->flush();
    }

    /**
     * プラグイン無効時の処理
     *
     * @param $config
     * @param Application $app
     * @throws \Exception
     */
    public function disable($config, Application $app)
    {
        $Payment = $app['eccube.repository.payment']->findOneBy(['method' => self::PAYMENT_METHOD]);
        if (!is_null($Payment)) {
            $PaymentOptions = $app['eccube.repository.payment_option']->findAll();
            foreach ($PaymentOptions as $paymentOption) {
                if ($paymentOption->getPaymentId() == $Payment->getId()) {
                    $app['orm.em']->remove($paymentOption);
                }
            }
            $app['orm.em']->flush();
        }
    }

    /**
     * プラグイン更新時の処理
     *
     * @param $config
     * @param Application $app
     * @throws \Exception
     */
    public function update($config, Application $app)
    {
    }

}
