<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260503170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create reusable user content tables for resumes and portfolios';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE experiences (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, company VARCHAR(180) NOT NULL, role VARCHAR(180) NOT NULL, description LONGTEXT DEFAULT NULL, location VARCHAR(180) DEFAULT NULL, start_date DATE DEFAULT NULL COMMENT '(DC2Type:date_immutable)', end_date DATE DEFAULT NULL COMMENT '(DC2Type:date_immutable)', is_current TINYINT(1) NOT NULL, sort_order INT NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_EXPERIENCE_USER (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql("CREATE TABLE educations (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, institution VARCHAR(180) NOT NULL, degree VARCHAR(180) DEFAULT NULL, field_of_study VARCHAR(180) DEFAULT NULL, description LONGTEXT DEFAULT NULL, start_date DATE DEFAULT NULL COMMENT '(DC2Type:date_immutable)', end_date DATE DEFAULT NULL COMMENT '(DC2Type:date_immutable)', is_current TINYINT(1) NOT NULL, sort_order INT NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_EDUCATION_USER (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql("CREATE TABLE skills (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, name VARCHAR(120) NOT NULL, category VARCHAR(120) DEFAULT NULL, level VARCHAR(40) DEFAULT NULL, sort_order INT NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_SKILL_USER (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql("CREATE TABLE certifications (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, name VARCHAR(180) NOT NULL, issuer VARCHAR(180) DEFAULT NULL, credential_url VARCHAR(255) DEFAULT NULL, issued_at DATE DEFAULT NULL COMMENT '(DC2Type:date_immutable)', expires_at DATE DEFAULT NULL COMMENT '(DC2Type:date_immutable)', sort_order INT NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_CERTIFICATION_USER (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql("CREATE TABLE projects (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, name VARCHAR(180) NOT NULL, description LONGTEXT DEFAULT NULL, project_url VARCHAR(255) DEFAULT NULL, repository_url VARCHAR(255) DEFAULT NULL, start_date DATE DEFAULT NULL COMMENT '(DC2Type:date_immutable)', end_date DATE DEFAULT NULL COMMENT '(DC2Type:date_immutable)', is_current TINYINT(1) NOT NULL, sort_order INT NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_PROJECT_USER (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql('ALTER TABLE experiences ADD CONSTRAINT FK_EXPERIENCE_USER FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE educations ADD CONSTRAINT FK_EDUCATION_USER FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE skills ADD CONSTRAINT FK_SKILL_USER FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE certifications ADD CONSTRAINT FK_CERTIFICATION_USER FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE projects ADD CONSTRAINT FK_PROJECT_USER FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE experiences DROP FOREIGN KEY FK_EXPERIENCE_USER');
        $this->addSql('ALTER TABLE educations DROP FOREIGN KEY FK_EDUCATION_USER');
        $this->addSql('ALTER TABLE skills DROP FOREIGN KEY FK_SKILL_USER');
        $this->addSql('ALTER TABLE certifications DROP FOREIGN KEY FK_CERTIFICATION_USER');
        $this->addSql('ALTER TABLE projects DROP FOREIGN KEY FK_PROJECT_USER');
        $this->addSql('DROP TABLE experiences');
        $this->addSql('DROP TABLE educations');
        $this->addSql('DROP TABLE skills');
        $this->addSql('DROP TABLE certifications');
        $this->addSql('DROP TABLE projects');
    }
}