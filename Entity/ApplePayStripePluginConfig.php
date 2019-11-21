<?php

namespace Plugin\ApplePayStripePlugin\Entity;

use Doctrine\ORM\Mapping as ORM;
use DateTime;
use Eccube\Entity\Customer;
use Eccube\Util\EntityUtil;

class ApplePayStripePluginConfig extends \Eccube\Entity\AbstractEntity
{
    /**
     * @var int
     */
    private $id = 1;

    /**
     * @var string
     */
    private $api_key;

    /**
     * @var string
     */
    private $api_key_secret;

    /**
     * @var string
     */
    private $order_button_placeholder;

    /**
     * @var created_at
     */
    private $created_at;

    /**
     * ApplePayStripePluginConfig constructor.
     */
    public function __construct()
    {
        $this->created_at = new DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->api_key;
    }

    /**
     * @param string $api_key
     */
    public function setApiKey($api_key)
    {
        $this->api_key = $api_key;
    }

    /**
     * @return string
     */
    public function getApiKeySecret()
    {
        return $this->api_key_secret;
    }

    /**
     * @param string $api_key_secret
     */
    public function setApiKeySecret($api_key_secret)
    {
        $this->api_key_secret = $api_key_secret;
    }

    /**
     * @return string
     */
    public function getOrderButtonPlaceholder()
    {
        return $this->order_button_placeholder;
    }

    /**
     * @param string $order_button_placeholder
     */
    public function setOrderButtonPlaceholder($order_button_placeholder)
    {
        $this->order_button_placeholder = $order_button_placeholder;
    }

    /**
     * @return created_at
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @param created_at $created_at
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
    }
    
}
