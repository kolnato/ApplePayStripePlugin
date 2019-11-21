<?php

namespace Plugin\ApplePayStripePlugin\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OrderStripeCharge
 */
class OrderStripeCharge extends \Eccube\Entity\AbstractEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $stripe_charge_id;


    /**
     * Set id
     *
     * @param integer $id
     * @return OrderStripeCharge
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set stripe_charge_id
     *
     * @param string $stripeChargeId
     * @return OrderStripeCharge
     */
    public function setStripeChargeId($stripeChargeId)
    {
        $this->stripe_charge_id = $stripeChargeId;

        return $this;
    }

    /**
     * Get stripe_charge_id
     *
     * @return string 
     */
    public function getStripeChargeId()
    {
        return $this->stripe_charge_id;
    }
}
