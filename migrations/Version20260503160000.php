<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260503160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create login sessions table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE login_sessions (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, login_date_time DATETIME NOT NULL, expire_date_time DATETIME NOT NULL, ip VARCHAR(45) DEFAULT NULL, token_hash VARCHAR(64) NOT NULL, session_metadata JSON NOT NULL, revoked_at DATETIME DEFAULT NULL, INDEX idx_login_sessions_token_hash (token_hash), INDEX idx_login_sessions_user_id (user_id), INDEX idx_login_sessions_expire_date_time (expire_date_time), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE login_sessions');
    }
}