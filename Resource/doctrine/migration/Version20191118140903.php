<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20191118140903 extends AbstractMigration
{
    const NAME = "dtb_payment";

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        if ($schema->hasTable(self::NAME)) {
            $this->addSql('INSERT INTO ' . self::NAME . ' (creator_id, payment_method, charge, create_date, update_date) VALUES (1, \'Apple Pay\', 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)');
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
