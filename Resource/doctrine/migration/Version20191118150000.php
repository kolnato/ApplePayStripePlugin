<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20191118150000 extends AbstractMigration
{
    protected $entities = array(
        'Plugin\ApplePayStripePlugin\Entity\ApplePayStripePluginConfig',
    );

    public function up(Schema $schema)
    {
        $this->dropTables($schema);
        if ($this->connection->getDatabasePlatform()->getName() == "postgresql") {
            $this->dropSequences($schema);
        }
        $this->createTables($schema);
    }

    public function down(Schema $schema)
    {
        $this->dropTables($schema);
        if ($this->connection->getDatabasePlatform()->getName() == "postgresql") {
            $this->dropSequences($schema);
        }
    }

    private function dropTables(Schema $schema) {
        $tableNames = $schema->getTableNames();
        $dropTableNames = array(
            'plg_apple_pay_stripe_plugin_config',
        );

        foreach ($dropTableNames as $drop) {
            if ($schema->hasTable($drop)) {
                $schema->dropTable($drop);
            }
        }
    }

    private function dropSequences(Schema $schema) {
        $targetSequences = array(
            'plg_apple_pay_stripe_plugin_config_id_seq',
        );
        foreach ($targetSequences as $seq) {
            if ($schema->hasSequence($seq)) {
                $schema->dropSequence($seq);
            }
        }
    }

    private function createTables(Schema $schema)
    {
        $table = $schema->createTable("plg_apple_pay_stripe_plugin_config");
        $table->addColumn('id', 'integer', array('notnull' => true));
        $table->addColumn('api_key', 'string', array('notnull' => true));
        $table->addColumn('api_key_secret', 'string', array('notnull' => true));
        $table->addColumn('order_button_placeholder', 'string', array('notnull' => true));
        $table->addColumn('created_at', 'datetime');
        $table->setPrimaryKey(array('id'));
    }
}
