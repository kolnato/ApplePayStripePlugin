<?php

/*
 * This file is part of the ApplePayStripePlugin
 *
 * Copyright (C) [year] [author]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Eccube\Application;
use Eccube\Common\Constant;

class Version20191126135710 extends AbstractMigration
{
    protected $entities = array(
        'Plugin\ApplePayStripePlugin\Entity\OrderStripeCharge'
    );

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->createTablePlgApplepaystripepluginOrderstripecharge($schema);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $schema->dropTable('plg_applepaystripeplugin_orderstripecharge');
    }

    /**
     * @param EntityManager $em
     * @return array
     * @throws \Doctrine\Common\Persistence\Mapping\MappingException
     */
    protected function getMetadata(EntityManager $em)
    {
        $meta = array();
        foreach ($this->entities as $entity) {
            $meta[] = $em->getMetadataFactory()->getMetadataFor($entity);
        }

        return $meta;
    }


    /**
     * @param Schema $schema
     */
    public function createTablePlgApplepaystripepluginOrderstripecharge(Schema $schema)
    {
        $table = $schema->createTable('plg_applepaystripeplugin_orderstripecharge');
        $table->addColumn('order_id', 'integer', array(
            'notnull' => false,
        ));
        $table->addColumn('stripe_charge_id', 'text', array(
            'notnull' => false,
        ));
    }

}
