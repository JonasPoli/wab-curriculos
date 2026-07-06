<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260703155218 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $createTableIfNotExists = function (string $tableName, string $sql) use ($schema) {
            if (!$schema->hasTable($tableName)) {
                $this->addSql($sql);
            }
        };

        $createTableIfNotExists('academic_background', 'CREATE TABLE academic_background (id INT AUTO_INCREMENT NOT NULL, candidate_id INT NOT NULL, education_level VARCHAR(60) NOT NULL, institution VARCHAR(150) NOT NULL, course VARCHAR(150) NOT NULL, status VARCHAR(30) NOT NULL, start_date DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', end_date DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', INDEX IDX_5A8804E491BD8781 (candidate_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $createTableIfNotExists('area_of_interest', 'CREATE TABLE area_of_interest (id INT AUTO_INCREMENT NOT NULL, tenant_id INT NOT NULL, title VARCHAR(100) NOT NULL, position INT DEFAULT 0 NOT NULL, INDEX IDX_FB9E46FC9033212A (tenant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $createTableIfNotExists('candidate', 'CREATE TABLE candidate (id INT AUTO_INCREMENT NOT NULL, tenant_id INT NOT NULL, user_id INT DEFAULT NULL, name VARCHAR(150) NOT NULL, birth_date DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', email VARCHAR(180) NOT NULL, phone VARCHAR(20) NOT NULL, city VARCHAR(100) NOT NULL, state VARCHAR(2) NOT NULL, linkedin_url VARCHAR(255) DEFAULT NULL, lattes_url VARCHAR(255) DEFAULT NULL, active_registration TINYINT(1) DEFAULT 0 NOT NULL, council_name VARCHAR(50) DEFAULT NULL, registration_number VARCHAR(30) DEFAULT NULL, professional_summary LONGTEXT DEFAULT NULL, contract_types JSON DEFAULT NULL, immediate_start TINYINT(1) DEFAULT 0 NOT NULL, resume_filename VARCHAR(255) DEFAULT NULL, candidate_message LONGTEXT DEFAULT NULL, monday_morning TINYINT(1) DEFAULT 0 NOT NULL, monday_afternoon TINYINT(1) DEFAULT 0 NOT NULL, tuesday_morning TINYINT(1) DEFAULT 0 NOT NULL, tuesday_afternoon TINYINT(1) DEFAULT 0 NOT NULL, wednesday_morning TINYINT(1) DEFAULT 0 NOT NULL, wednesday_afternoon TINYINT(1) DEFAULT 0 NOT NULL, thursday_morning TINYINT(1) DEFAULT 0 NOT NULL, thursday_afternoon TINYINT(1) DEFAULT 0 NOT NULL, friday_morning TINYINT(1) DEFAULT 0 NOT NULL, friday_afternoon TINYINT(1) DEFAULT 0 NOT NULL, lgpd_consent TINYINT(1) DEFAULT 0 NOT NULL, lgpd_consent_at DATETIME DEFAULT NULL, lgpd_consent_ip VARCHAR(50) DEFAULT NULL, lgpd_consent_user_agent VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL, INDEX IDX_C8B28E449033212A (tenant_id), UNIQUE INDEX UNIQ_C8B28E44A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $createTableIfNotExists('candidate_career', 'CREATE TABLE candidate_career (candidate_id INT NOT NULL, career_id INT NOT NULL, INDEX IDX_668325891BD8781 (candidate_id), INDEX IDX_6683258B58CDA09 (career_id), PRIMARY KEY(candidate_id, career_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $createTableIfNotExists('career', 'CREATE TABLE career (id INT AUTO_INCREMENT NOT NULL, area_id INT NOT NULL, title VARCHAR(150) NOT NULL, active TINYINT(1) DEFAULT 1 NOT NULL, position INT DEFAULT 0 NOT NULL, INDEX IDX_B25B6C84BD0F409C (area_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $createTableIfNotExists('example_entity', 'CREATE TABLE example_entity (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, some_bool TINYINT(1) NOT NULL, some_list JSON DEFAULT NULL, some_date DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', sometime TIME DEFAULT NULL COMMENT \'(DC2Type:time_immutable)\', some_datetime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', status INT NOT NULL, INDEX IDX_AFE7E950A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $createTableIfNotExists('example_entity_user', 'CREATE TABLE example_entity_user (example_entity_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_C6E00128BB2FD451 (example_entity_id), INDEX IDX_C6E00128A76ED395 (user_id), PRIMARY KEY(example_entity_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $createTableIfNotExists('exclusion_request', 'CREATE TABLE exclusion_request (id INT AUTO_INCREMENT NOT NULL, tenant_id INT NOT NULL, email VARCHAR(180) NOT NULL, token VARCHAR(100) NOT NULL, confirmed TINYINT(1) DEFAULT 0 NOT NULL, requested_at DATETIME NOT NULL, confirmed_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_569CD7995F37A13B (token), INDEX IDX_569CD7999033212A (tenant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $createTableIfNotExists('image', 'CREATE TABLE image (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) DEFAULT NULL, image_name VARCHAR(255) DEFAULT NULL, updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $createTableIfNotExists('lgpd_log', 'CREATE TABLE lgpd_log (id INT AUTO_INCREMENT NOT NULL, candidate_id INT DEFAULT NULL, action_type VARCHAR(30) NOT NULL, executed_at DATETIME NOT NULL, ip_address VARCHAR(50) DEFAULT NULL, details LONGTEXT DEFAULT NULL, INDEX IDX_28F76A9991BD8781 (candidate_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $createTableIfNotExists('tenant', 'CREATE TABLE tenant (id INT AUTO_INCREMENT NOT NULL, domain VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, logo VARCHAR(255) DEFAULT NULL, dark_logo VARCHAR(255) DEFAULT NULL, favicon VARCHAR(255) DEFAULT NULL, primary_color VARCHAR(7) DEFAULT \'#0044cc\', secondary_color VARCHAR(7) DEFAULT \'#ffaa00\', primary_color_dark VARCHAR(7) DEFAULT \'#3b82f6\', secondary_color_dark VARCHAR(7) DEFAULT \'#fbbf24\', contact_email VARCHAR(255) DEFAULT NULL, phone VARCHAR(50) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, active_theme VARCHAR(50) DEFAULT \'moderno\', seo_title VARCHAR(255) DEFAULT NULL, seo_description LONGTEXT DEFAULT NULL, recaptcha_site_key VARCHAR(255) DEFAULT NULL, recaptcha_secret_key VARCHAR(255) DEFAULT NULL, lgpd_terms_html LONGTEXT DEFAULT NULL, privacy_policy_html LONGTEXT DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_4E59C462A7A91E0B (domain), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $createTableIfNotExists('work_experience', 'CREATE TABLE work_experience (id INT AUTO_INCREMENT NOT NULL, candidate_id INT NOT NULL, company_name VARCHAR(150) NOT NULL, position VARCHAR(150) NOT NULL, start_date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', end_date DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', current_job TINYINT(1) DEFAULT 0 NOT NULL, description LONGTEXT NOT NULL, INDEX IDX_1EF36CD091BD8781 (candidate_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $createTableIfNotExists('messenger_messages', 'CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        if (!$schema->hasTable('user')) {
            $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, tenant_id INT DEFAULT NULL, username VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, name VARCHAR(255) DEFAULT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, work_group INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL, reset_password_token VARCHAR(100) DEFAULT NULL, reset_password_expires_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649452C9EC5 (reset_password_token), INDEX IDX_8D93D6499033212A (tenant_id), UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME (username), UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        } else {
            $userTable = $schema->getTable('user');
            if (!$userTable->hasColumn('tenant_id')) {
                $this->addSql('ALTER TABLE user ADD tenant_id INT DEFAULT NULL');
            }
            if (!$userTable->hasIndex('IDX_8D93D6499033212A')) {
                $this->addSql('CREATE INDEX IDX_8D93D6499033212A ON user (tenant_id)');
            }
            if (!$userTable->hasColumn('work_group')) {
                $this->addSql('ALTER TABLE user ADD work_group INT DEFAULT NULL');
            }
        }

        $addForeignKey = function (string $tableName, string $fkName, string $sql) use ($schema) {
            if ($schema->hasTable($tableName)) {
                $table = $schema->getTable($tableName);
                if (!$table->hasForeignKey($fkName)) {
                    $this->addSql($sql);
                }
            } else {
                $this->addSql($sql);
            }
        };

        $addForeignKey('academic_background', 'FK_5A8804E491BD8781', 'ALTER TABLE academic_background ADD CONSTRAINT FK_5A8804E491BD8781 FOREIGN KEY (candidate_id) REFERENCES candidate (id) ON DELETE CASCADE');
        $addForeignKey('area_of_interest', 'FK_FB9E46FC9033212A', 'ALTER TABLE area_of_interest ADD CONSTRAINT FK_FB9E46FC9033212A FOREIGN KEY (tenant_id) REFERENCES tenant (id) ON DELETE CASCADE');
        $addForeignKey('candidate', 'FK_C8B28E449033212A', 'ALTER TABLE candidate ADD CONSTRAINT FK_C8B28E449033212A FOREIGN KEY (tenant_id) REFERENCES tenant (id) ON DELETE CASCADE');
        $addForeignKey('candidate', 'FK_C8B28E44A76ED395', 'ALTER TABLE candidate ADD CONSTRAINT FK_C8B28E44A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL');
        $addForeignKey('candidate_career', 'FK_668325891BD8781', 'ALTER TABLE candidate_career ADD CONSTRAINT FK_668325891BD8781 FOREIGN KEY (candidate_id) REFERENCES candidate (id) ON DELETE CASCADE');
        $addForeignKey('candidate_career', 'FK_6683258B58CDA09', 'ALTER TABLE candidate_career ADD CONSTRAINT FK_6683258B58CDA09 FOREIGN KEY (career_id) REFERENCES career (id) ON DELETE CASCADE');
        $addForeignKey('career', 'FK_B25B6C84BD0F409C', 'ALTER TABLE career ADD CONSTRAINT FK_B25B6C84BD0F409C FOREIGN KEY (area_id) REFERENCES area_of_interest (id) ON DELETE CASCADE');
        $addForeignKey('example_entity', 'FK_AFE7E950A76ED395', 'ALTER TABLE example_entity ADD CONSTRAINT FK_AFE7E950A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $addForeignKey('example_entity_user', 'FK_C6E00128BB2FD451', 'ALTER TABLE example_entity_user ADD CONSTRAINT FK_C6E00128BB2FD451 FOREIGN KEY (example_entity_id) REFERENCES example_entity (id) ON DELETE CASCADE');
        $addForeignKey('example_entity_user', 'FK_C6E00128A76ED395', 'ALTER TABLE example_entity_user ADD CONSTRAINT FK_C6E00128A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $addForeignKey('exclusion_request', 'FK_569CD7999033212A', 'ALTER TABLE exclusion_request ADD CONSTRAINT FK_569CD7999033212A FOREIGN KEY (tenant_id) REFERENCES tenant (id) ON DELETE CASCADE');
        $addForeignKey('lgpd_log', 'FK_28F76A9991BD8781', 'ALTER TABLE lgpd_log ADD CONSTRAINT FK_28F76A9991BD8781 FOREIGN KEY (candidate_id) REFERENCES candidate (id) ON DELETE SET NULL');
        $addForeignKey('user', 'FK_8D93D6499033212A', 'ALTER TABLE user ADD CONSTRAINT FK_8D93D6499033212A FOREIGN KEY (tenant_id) REFERENCES tenant (id) ON DELETE SET NULL');
        $addForeignKey('work_experience', 'FK_1EF36CD091BD8781', 'ALTER TABLE work_experience ADD CONSTRAINT FK_1EF36CD091BD8781 FOREIGN KEY (candidate_id) REFERENCES candidate (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE academic_background DROP FOREIGN KEY FK_5A8804E491BD8781');
        $this->addSql('ALTER TABLE area_of_interest DROP FOREIGN KEY FK_FB9E46FC9033212A');
        $this->addSql('ALTER TABLE candidate DROP FOREIGN KEY FK_C8B28E449033212A');
        $this->addSql('ALTER TABLE candidate DROP FOREIGN KEY FK_C8B28E44A76ED395');
        $this->addSql('ALTER TABLE candidate_career DROP FOREIGN KEY FK_668325891BD8781');
        $this->addSql('ALTER TABLE candidate_career DROP FOREIGN KEY FK_6683258B58CDA09');
        $this->addSql('ALTER TABLE career DROP FOREIGN KEY FK_B25B6C84BD0F409C');
        $this->addSql('ALTER TABLE example_entity DROP FOREIGN KEY FK_AFE7E950A76ED395');
        $this->addSql('ALTER TABLE example_entity_user DROP FOREIGN KEY FK_C6E00128BB2FD451');
        $this->addSql('ALTER TABLE example_entity_user DROP FOREIGN KEY FK_C6E00128A76ED395');
        $this->addSql('ALTER TABLE exclusion_request DROP FOREIGN KEY FK_569CD7999033212A');
        $this->addSql('ALTER TABLE lgpd_log DROP FOREIGN KEY FK_28F76A9991BD8781');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6499033212A');
        $this->addSql('ALTER TABLE work_experience DROP FOREIGN KEY FK_1EF36CD091BD8781');
        $this->addSql('DROP TABLE academic_background');
        $this->addSql('DROP TABLE area_of_interest');
        $this->addSql('DROP TABLE candidate');
        $this->addSql('DROP TABLE candidate_career');
        $this->addSql('DROP TABLE career');
        $this->addSql('DROP TABLE example_entity');
        $this->addSql('DROP TABLE example_entity_user');
        $this->addSql('DROP TABLE exclusion_request');
        $this->addSql('DROP TABLE image');
        $this->addSql('DROP TABLE lgpd_log');
        $this->addSql('DROP TABLE tenant');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE work_experience');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
