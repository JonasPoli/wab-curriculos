<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260706143800 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adiciona campos header_script e body_script na tabela tenant';
    }

    public function up(Schema $schema): void
    {
        if ($schema->hasTable('tenant')) {
            $table = $schema->getTable('tenant');
            if (!$table->hasColumn('header_script')) {
                $this->addSql('ALTER TABLE tenant ADD header_script LONGTEXT DEFAULT NULL');
            }
            if (!$table->hasColumn('body_script')) {
                $this->addSql('ALTER TABLE tenant ADD body_script LONGTEXT DEFAULT NULL');
            }
        } else {
            $this->addSql('ALTER TABLE tenant ADD header_script LONGTEXT DEFAULT NULL, ADD body_script LONGTEXT DEFAULT NULL');
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tenant DROP header_script, DROP body_script');
    }
}
