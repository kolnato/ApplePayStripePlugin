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

use Eccube\Application;
use Symfony\Component\HttpFoundation\Request;

class ConfigController
{

    /**
     * ApplePayStripePlugin用設定画面
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Application $app, Request $request)
    {

        $form = $app['form.factory']->createBuilder('applepaystripeplugin_config')->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // add code...
        }

        return $app->render('ApplePayStripePlugin/Resource/template/admin/config.twig', array(
            'form' => $form->createView(),
        ));
    }

}
