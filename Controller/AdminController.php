<?php

namespace Plugin\ApplePayStripePlugin\Controller;

use Eccube\Application;
use Eccube\Application\ApplicationTrait;
use Symfony\Component\HttpFoundation\Request;
use Eccube\Controller\AbstractController;
use Plugin\ApplePayStripePlugin\Entity\ApplePayStripePluginConfig;
use Plugin\ApplePayStripePlugin\Entity;


class AdminController extends AbstractController {
    const PAGE_UNIT = 20;

    private $app;

    private $locale;

    public function config(ApplicationTrait $app, Request $request) {
        $this->initCommon($app);

        $ApplePayStripePluginConfig = $this->app['orm.em']->getRepository('Plugin\ApplePayStripePlugin\Entity\ApplePayStripePluginConfig')->findOneBy(array('id' => 1));
        if (is_null($ApplePayStripePluginConfig)) {
            $ApplePayStripePluginConfig = new ApplePayStripePluginConfig();
            $ApplePayStripePluginConfig->setApiKey('YOUR_API_KEY');
            $ApplePayStripePluginConfig->setApiKeySecret('YOUR_SECRET_KEY');
            $ApplePayStripePluginConfig->setOrderButtonPlaceholder('order-button');
            $this->app['orm.em']->persist($ApplePayStripePluginConfig);
            $this->app['orm.em']->flush($ApplePayStripePluginConfig);
        }
        $form = $app['form.factory']->createBuilder('apple_pay_stripe_plugin_config', $ApplePayStripePluginConfig)->getForm();
        $form->setData($ApplePayStripePluginConfig);

        return $this->app->render(
            'ApplePayStripePlugin/Resource/template/admin/config.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }
    
    public function config_update(ApplicationTrait $app, Request $request) {
        $this->initCommon($app);

        $form = $this->app['form.factory']->createBuilder('apple_pay_stripe_plugin_config')->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $request->request->get('apple_pay_stripe_plugin_config');
            $ApplePayStripePluginConfig = $this->app['orm.em']->getRepository('Plugin\ApplePayStripePlugin\Entity\ApplePayStripePluginConfig')->findOneBy(array('id' => 1));
            $ApplePayStripePluginConfig->setApiKey($data['api_key']);
            $ApplePayStripePluginConfig->setApiKeySecret($data['api_key_secret']);
            $ApplePayStripePluginConfig->setOrderButtonPlaceholder($data['order_button_placeholder']);
            $this->app['orm.em']->persist($ApplePayStripePluginConfig);
            $this->app['orm.em']->flush($ApplePayStripePluginConfig);
        }

        return $this->app->render(
            'ApplePayStripePlugin/Resource/template/admin/config.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }

/*
    public function log(ApplicationTrait $app, Request $request) {
        $this->initCommon($app);

        $page = $request->get('page', 0);

        $qb = $this->app['orm.em']->createQueryBuilder();
        $qb->select($qb->expr()->count('c'))->from('Plugin\ApplePayStripePlugin\Entity\ApplePayStripePluginLog', 'c');
        $count = $qb->getQuery()->getSingleScalarResult();
        $last_page = floor(($count - 1) / self::PAGE_UNIT);

        $logs = $this->app['orm.em']->getRepository('Plugin\ApplePayStripePlugin\Entity\ApplePayStripePluginLog')->findBy(array(), array('id' =>  'DESC'), self::PAGE_UNIT, self::PAGE_UNIT * $page);

        return $this->app->render(
            'ApplePayStripePlugin/Resource/template/log.twig',
            array(
                'page' => $page,
                'logs' => $logs,
                'count' => $count,
                'last_page' => $last_page,
            )
        );
    }
*/

    private function initCommon(ApplicationTrait $app) {
        $this->app = $app;
        if (preg_match('/^ja/', $this->app['config']['locale'])) {
            $this->locale = 'ja';
        }
    }
}
