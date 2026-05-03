<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260503162000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create user profiles table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE user_profiles (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, headline VARCHAR(180) DEFAULT NULL, bio LONGTEXT DEFAULT NULL, phone VARCHAR(30) DEFAULT NULL, city VARCHAR(120) DEFAULT NULL, state VARCHAR(80) DEFAULT NULL, country VARCHAR(80) DEFAULT NULL, linkedin_url VARCHAR(255) DEFAULT NULL, github_url VARCHAR(255) DEFAULT NULL, website_url VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_USER_PROFILE_USER (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE user_profiles ADD CONSTRAINT FK_USER_PROFILES_USER FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user_profiles DROP FOREIGN KEY FK_USER_PROFILES_USER');
        $this->addSql('DROP TABLE user_profiles');
    }
}