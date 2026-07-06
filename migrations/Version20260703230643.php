<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260703230643 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        if ($schema->hasTable('candidate')) {
            $table = $schema->getTable('candidate');
            if (!$table->hasColumn('reset_token')) {
                $this->addSql('ALTER TABLE candidate ADD reset_token VARCHAR(100) DEFAULT NULL');
            }
            if (!$table->hasColumn('reset_token_expires_at')) {
                $this->addSql('ALTER TABLE candidate ADD reset_token_expires_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
            }
            if (!$table->hasIndex('UNIQ_C8B28E44D7C8DC19')) {
                $this->addSql('CREATE UNIQUE INDEX UNIQ_C8B28E44D7C8DC19 ON candidate (reset_token)');
            }
        } else {
            $this->addSql('ALTER TABLE candidate ADD reset_token VARCHAR(100) DEFAULT NULL, ADD reset_token_expires_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
            $this->addSql('CREATE UNIQUE INDEX UNIQ_C8B28E44D7C8DC19 ON candidate (reset_token)');
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_C8B28E44D7C8DC19 ON candidate');
        $this->addSql('ALTER TABLE candidate DROP reset_token, DROP reset_token_expires_at');
    }
}
