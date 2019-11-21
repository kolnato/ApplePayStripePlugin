<?php

/*
 * This file is part of the ApplePayStripePlugin
 *
 * Copyright (C) 2019 binaryquaver
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ApplePayStripePlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ConfigType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $data = $options['data'];
        if (!is_null($data)) {
            $api_key = $data->getApiKey();
            $api_key_secret = $data->getApiKeySecret();
            $order_button_placeholder = $data->getOrderButtonPlaceholder();
        }
        $builder
            ->add('api_key', 'text', array(
                'label' => '公開キー',
                'required' => true,
                'data' => $api_key,
                'attr' => array(
                    'class' => 'apple_pay_stripe_plugin_config',
                ),
                'mapped' => false,
                'constraints' => array(
                    new Assert\NotBlank(array(
                            'message' => '公開キーが入力されていません。')
                    ),
                    new Assert\Regex(array(
                            'pattern' => '/^\w+$/',
                            'match' => true,
                            'message' => '公開キーは半角英数字で入力してください。')
                    ),
                ),
            ))
            ->add('api_key_secret', 'text', array(
                'label' => '秘密キー',
                'required' => true,
                'data' => $api_key_secret,
                'attr' => array(
                    'class' => 'apple_pay_stripe_plugin_config',
                ),
                'mapped' => false,
                'constraints' => array(
                    new Assert\NotBlank(array(
                            'message' => '秘密キーが入力されていません。')
                    ),
                    new Assert\Regex(array(
                            'pattern' => '/^\w+$/',
                            'match' => true,
                            'message' => '秘密キーは半角英数字で入力してください。')
                    ),
                ),
            ))
            ->add('order_button_placeholder', 'text', array(
                'label' => 'ApplePayボタン設置位置',
                'required' => true,
                'data' => $order_button_placeholder,
                'attr' => array(
                    'class' => 'apple_pay_stripe_plugin_config',
                ),
                'mapped' => false,
                'constraints' => array(
                    new Assert\NotBlank(array(
                            'message' => 'ApplePayボタン設置位置が入力されていません。')
                    ),
                ),
            ));

    }

    public function getName()
    {
        return 'apple_pay_stripe_plugin_config';
    }

}
