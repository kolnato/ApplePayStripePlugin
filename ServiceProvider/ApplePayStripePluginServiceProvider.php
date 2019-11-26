<?php

/*
 * This file is part of the ApplePayStripePlugin
 *
 * Copyright (C) 2019 binaryquaver
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ApplePayStripePlugin\ServiceProvider;

use Monolog\Handler\FingersCrossed\ErrorLevelActivationStrategy;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Plugin\ApplePayStripePlugin\Form\Type\ConfigType;
use Silex\Application as BaseApplication;
use Silex\ServiceProviderInterface;

class ApplePayStripePluginServiceProvider implements ServiceProviderInterface
{

    public function register(BaseApplication $app)
    {
        // Repository
        $app['apple_pay_stripe_plugin.repository.apple_pay_stripe_plugin_config'] = $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\ApplePayStripePlugin\Entity\ApplePayStripePluginConfig');
        });
        $app['apple_pay_stripe_plugin.repository.order_stripe_charge'] = $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\ApplePayStripePlugin\Entity\OrderStripeCharge');
        });

        // プラグイン用設定画面
        $app->match('/' . $app["config"]["admin_route"]  . '/plugin/apple_pay_stripe_plugin/config', '\Plugin\ApplePayStripePlugin\Controller\AdminController::config')->bind('apple_pay_stripe_plugin_config');
        $app->match('/' . $app["config"]["admin_route"]  . '/plugin/apple_pay_stripe_plugin/config_update', '\Plugin\ApplePayStripePlugin\Controller\AdminController::config_update')->bind('apple_pay_stripe_plugin_config_update');

        // 独自コントローラ
        $app->match('/plugin/applepaystripeplugin/payment', 'Plugin\ApplePayStripePlugin\Controller\ApplePayStripePluginController::payment')->bind('plugin_ApplePayStripePlugin_payment');

        // Form
        $app['form.types'] = $app->share($app->extend('form.types', function ($types) use ($app) {
            $types[] = new ConfigType();
            return $types;
        }));

        $app['config'] = $app->share($app->extend('config', function ($config) {
            $head = array_slice($config['nav'], 0, 4);
            $tail = array_slice($config['nav'], 4);
            $append = array(array(
                'id' => 'apple_pay_stripe_plugin',
                'name' => 'ApplePay+Stripe 管理',
                'has_child' => 'true',
                'icon' => "cb-point",
                'child' => array(
                    array(
                        'id' => 'apple_pay_stripe_plugin_config',
                        'name' => 'APIキー',
                        'url' => 'apple_pay_stripe_plugin_config'
                    ),
                )
            ));
            $config['nav'] = array_merge($head, $append, $tail);
            return $config;
        }));


        // Service

        // メッセージ登録
        // $file = __DIR__ . '/../Resource/locale/message.' . $app['locale'] . '.yml';
        // $app['translator']->addResource('yaml', $file, $app['locale']);

        // load config
        // プラグイン独自の定数はconfig.ymlの「const」パラメータに対して定義し、$app['applepaystripepluginconfig']['定数名']で利用可能
        // if (isset($app['config']['ApplePayStripePlugin']['const'])) {
        //     $config = $app['config'];
        //     $app['applepaystripepluginconfig'] = $app->share(function () use ($config) {
        //         return $config['ApplePayStripePlugin']['const'];
        //     });
        // }

        // ログファイル設定
        $app['monolog.logger.applepaystripeplugin'] = $app->share(function ($app) {

            $logger = new $app['monolog.logger.class']('applepaystripeplugin');

            $filename = $app['config']['root_dir'].'/app/log/applepaystripeplugin.log';
            $RotateHandler = new RotatingFileHandler($filename, $app['config']['log']['max_files'], Logger::INFO);
            $RotateHandler->setFilenameFormat(
                'applepaystripeplugin_{date}',
                'Y-m-d'
            );

            $logger->pushHandler(
                new FingersCrossedHandler(
                    $RotateHandler,
                    new ErrorLevelActivationStrategy(Logger::ERROR),
                    0,
                    true,
                    true,
                    Logger::INFO
                )
            );

            return $logger;
        });

    }

    public function boot(BaseApplication $app)
    {
    }

}
