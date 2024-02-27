<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231026154210 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__product AS SELECT id, name, price, ref, type, image_product FROM product');
        $this->addSql('DROP TABLE product');
        $this->addSql('CREATE TABLE product (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, price INTEGER NOT NULL, ref VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, image_product VARCHAR(255) DEFAULT NULL)');
        $this->addSql('INSERT INTO product (id, name, price, ref, type, image_product) SELECT id, name, price, ref, type, image_product FROM __temp__product');
        $this->addSql('DROP TABLE __temp__product');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__product AS SELECT id, name, price, ref, image_product, type FROM product');
        $this->addSql('DROP TABLE product');
        $this->addSql('CREATE TABLE product (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, price DOUBLE PRECISION NOT NULL, ref VARCHAR(255) NOT NULL, image_product VARCHAR(255) DEFAULT NULL, type VARCHAR(255) DEFAULT NULL)');
        $this->addSql('INSERT INTO product (id, name, price, ref, image_product, type) SELECT id, name, price, ref, image_product, type FROM __temp__product');
        $this->addSql('DROP TABLE __temp__product');
    }
}
