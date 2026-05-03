<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260503163000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create resumes table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE resumes (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, public_id VARCHAR(36) NOT NULL, title VARCHAR(180) NOT NULL, target_role VARCHAR(180) DEFAULT NULL, language VARCHAR(10) NOT NULL, ats_score INT DEFAULT NULL, is_main TINYINT(1) NOT NULL, is_public TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_RESUME_USER (user_id), UNIQUE INDEX UNIQ_RESUME_PUBLIC_ID (public_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql('ALTER TABLE resumes ADD CONSTRAINT FK_RESUME_USER FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE resumes DROP FOREIGN KEY FK_RESUME_USER');
        $this->addSql('DROP TABLE resumes');
    }
}