<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260703170315 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        if ($schema->hasTable('candidate')) {
            $table = $schema->getTable('candidate');
            if (!$table->hasColumn('password')) {
                $this->addSql('ALTER TABLE candidate ADD password VARCHAR(255) DEFAULT NULL');
            }
            if (!$table->hasColumn('roles')) {
                $this->addSql('ALTER TABLE candidate ADD roles JSON DEFAULT NULL');
            }
        } else {
            $this->addSql('ALTER TABLE candidate ADD password VARCHAR(255) DEFAULT NULL, ADD roles JSON DEFAULT NULL');
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE candidate DROP password, DROP roles');
    }
}
