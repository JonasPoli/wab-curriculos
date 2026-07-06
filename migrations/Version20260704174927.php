<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260704174927 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE saved_search (id INT AUTO_INCREMENT NOT NULL, tenant_id INT NOT NULL, user_id INT NOT NULL, name VARCHAR(100) NOT NULL, filters JSON NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_D0F6A0BC9033212A (tenant_id), INDEX IDX_D0F6A0BCA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE saved_search ADD CONSTRAINT FK_D0F6A0BC9033212A FOREIGN KEY (tenant_id) REFERENCES tenant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE saved_search ADD CONSTRAINT FK_D0F6A0BCA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tenant ADD hero_title VARCHAR(255) DEFAULT NULL, ADD hero_subtitle VARCHAR(255) DEFAULT NULL, ADD hero_description LONGTEXT DEFAULT NULL, ADD cta_text VARCHAR(100) DEFAULT NULL, ADD cta_subtext VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE saved_search DROP FOREIGN KEY FK_D0F6A0BC9033212A');
        $this->addSql('ALTER TABLE saved_search DROP FOREIGN KEY FK_D0F6A0BCA76ED395');
        $this->addSql('DROP TABLE saved_search');
        $this->addSql('ALTER TABLE tenant DROP hero_title, DROP hero_subtitle, DROP hero_description, DROP cta_text, DROP cta_subtext');
    }
}
