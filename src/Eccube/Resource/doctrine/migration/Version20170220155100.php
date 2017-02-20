<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170220155100 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("INSERT INTO dtb_page_layout (device_type_id, page_name, url, file_name, edit_flg, author, description, keyword, update_url, create_date, update_date, meta_robots) VALUES (10, 'お問い合わせ(確認ページ)', 'contact_confirm', 'Contact/confirm', 2, NULL, NULL, NULL, NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, NULL);");

        if ($this->connection->getDatabasePlatform()->getName() == "postgresql") {
            $this->addSql("SELECT setval('dtb_page_layout_page_id_seq', 32);");
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}
