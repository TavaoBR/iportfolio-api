<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260509183000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Resume sections, templates catalog, portfolio site/sections, ai_analyses; resume.template_key';
    }

    public function up(Schema $schema): void
    {
        if (!$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform) {
            return;
        }

        $this->addSql('ALTER TABLE resumes ADD template_key VARCHAR(120) DEFAULT NULL');

        $this->addSql("CREATE TABLE resume_sections (id INT AUTO_INCREMENT NOT NULL, resume_id INT NOT NULL, section_type VARCHAR(32) NOT NULL, title VARCHAR(180) DEFAULT NULL, content MEDIUMTEXT DEFAULT NULL, position INT NOT NULL, is_visible TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_RESUME_SECTIONS_RESUME (resume_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql('ALTER TABLE resume_sections ADD CONSTRAINT FK_RESUME_SECTIONS_RESUME FOREIGN KEY (resume_id) REFERENCES resumes (id) ON DELETE CASCADE');

        $this->addSql("CREATE TABLE templates (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(120) NOT NULL, template_key VARCHAR(64) NOT NULL, type VARCHAR(20) NOT NULL, preview_image VARCHAR(500) DEFAULT NULL, is_premium TINYINT(1) NOT NULL, is_active TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', UNIQUE INDEX UNIQ_TEMPLATES_KEY (template_key), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");

        $this->addSql("INSERT INTO templates (name, template_key, type, preview_image, is_premium, is_active, created_at) VALUES
            ('Curriculo classico', 'resume_classic', 'resume', NULL, 0, 1, NOW()),
            ('Curriculo minimal', 'resume_minimal', 'resume', NULL, 0, 1, NOW()),
            ('Portfolio grid', 'portfolio_grid', 'portfolio', NULL, 0, 1, NOW())");

        $this->addSql("CREATE TABLE portfolio_sites (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, slug VARCHAR(120) NOT NULL, template_key VARCHAR(120) DEFAULT NULL, title VARCHAR(180) NOT NULL, subtitle VARCHAR(255) DEFAULT NULL, is_public TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', UNIQUE INDEX UNIQ_PORTFOLIO_SLUG (slug), INDEX IDX_PORTFOLIO_USER (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql('ALTER TABLE portfolio_sites ADD CONSTRAINT FK_PORTFOLIO_USER FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');

        $this->addSql("CREATE TABLE portfolio_sections (id INT AUTO_INCREMENT NOT NULL, portfolio_site_id INT NOT NULL, section_type VARCHAR(32) NOT NULL, layout_type VARCHAR(32) NOT NULL, position INT NOT NULL, is_visible TINYINT(1) NOT NULL, settings_json JSON DEFAULT NULL COMMENT '(DC2Type:json)', created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_PORTFOLIO_SECTIONS_SITE (portfolio_site_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql('ALTER TABLE portfolio_sections ADD CONSTRAINT FK_PORTFOLIO_SECTIONS_SITE FOREIGN KEY (portfolio_site_id) REFERENCES portfolio_sites (id) ON DELETE CASCADE');

        $this->addSql("CREATE TABLE ai_analyses (id INT AUTO_INCREMENT NOT NULL, resume_id INT NOT NULL, analysis_type VARCHAR(32) NOT NULL, status VARCHAR(24) NOT NULL, request_payload JSON DEFAULT NULL COMMENT '(DC2Type:json)', result JSON DEFAULT NULL COMMENT '(DC2Type:json)', error_message VARCHAR(500) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_AI_ANALYSES_RESUME (resume_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql('ALTER TABLE ai_analyses ADD CONSTRAINT FK_AI_ANALYSES_RESUME FOREIGN KEY (resume_id) REFERENCES resumes (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        if (!$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform) {
            return;
        }

        $this->addSql('ALTER TABLE ai_analyses DROP FOREIGN KEY FK_AI_ANALYSES_RESUME');
        $this->addSql('DROP TABLE ai_analyses');
        $this->addSql('ALTER TABLE portfolio_sections DROP FOREIGN KEY FK_PORTFOLIO_SECTIONS_SITE');
        $this->addSql('DROP TABLE portfolio_sections');
        $this->addSql('ALTER TABLE portfolio_sites DROP FOREIGN KEY FK_PORTFOLIO_USER');
        $this->addSql('DROP TABLE portfolio_sites');
        $this->addSql('DROP TABLE templates');
        $this->addSql('ALTER TABLE resume_sections DROP FOREIGN KEY FK_RESUME_SECTIONS_RESUME');
        $this->addSql('DROP TABLE resume_sections');
        $this->addSql('ALTER TABLE resumes DROP template_key');
    }
}
