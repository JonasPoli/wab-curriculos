<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260625015053 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add email, name, created_at and updated_at to user table';
    }

    public function up(Schema $schema): void
    {
        // Add nullable first, set defaults, then make email NOT NULL
        $this->addSql("ALTER TABLE user ADD email VARCHAR(180) DEFAULT NULL, ADD name VARCHAR(255) DEFAULT NULL, ADD created_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', ADD updated_at DATETIME DEFAULT NULL");
        // Fill existing rows with safe defaults
        $this->addSql("UPDATE user SET email = CONCAT(username, '@placeholder.local'), created_at = NOW() WHERE email IS NULL OR created_at IS NULL");
        // Now enforce NOT NULL
        $this->addSql("ALTER TABLE user MODIFY email VARCHAR(180) NOT NULL, MODIFY created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'");
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON user (email)');
        $this->addSql('DROP INDEX IDX_75EA56E016BA31DB ON messenger_messages');
        $this->addSql('DROP INDEX IDX_75EA56E0E3BD61CE ON messenger_messages');
        $this->addSql('DROP INDEX IDX_75EA56E0FB7336F0 ON messenger_messages');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages (queue_name, available_at, delivered_at, id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('DROP INDEX UNIQ_IDENTIFIER_EMAIL ON user');
        $this->addSql('ALTER TABLE user DROP email, DROP name, DROP created_at, DROP updated_at');
    }
}
