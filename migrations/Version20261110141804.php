<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261110141804 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Índice único (tenant, e-mail) em candidate e FK user.tenant_id sem ON DELETE SET NULL';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CANDIDATE_TENANT_EMAIL ON candidate (tenant_id, email)');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6499033212A');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6499033212A FOREIGN KEY (tenant_id) REFERENCES tenant (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6499033212A');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6499033212A FOREIGN KEY (tenant_id) REFERENCES tenant (id) ON DELETE SET NULL');
        $this->addSql('DROP INDEX UNIQ_CANDIDATE_TENANT_EMAIL ON candidate');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
