<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220322103748 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE photo_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE warehouse_whitelist_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE photo (id INT NOT NULL, warehouse_message_id INT NOT NULL, whatsapp_identifier VARCHAR(255) NOT NULL, created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_14B7841889B287A ON photo (warehouse_message_id)');
        $this->addSql('CREATE TABLE warehouse_message (id INT NOT NULL, message_from VARCHAR(255) NOT NULL, text_body TEXT NOT NULL, profile_name VARCHAR(255) NOT NULL, wa_id BIGINT NOT NULL, status VARCHAR(1) NOT NULL, code VARCHAR(255) NOT NULL, timestamp BIGINT NOT NULL, created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, code2 VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE warehouse_whitelist (id INT NOT NULL, phone BIGINT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE photo ADD CONSTRAINT FK_14B7841889B287A FOREIGN KEY (warehouse_message_id) REFERENCES warehouse_message (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE photo DROP CONSTRAINT FK_14B7841889B287A');
        $this->addSql('DROP SEQUENCE photo_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE warehouse_whitelist_id_seq CASCADE');
        $this->addSql('DROP TABLE photo');
        $this->addSql('DROP TABLE warehouse_message');
        $this->addSql('DROP TABLE warehouse_whitelist');
    }
}
