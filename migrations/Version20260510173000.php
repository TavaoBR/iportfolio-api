<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260510173000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Template preview/bundle fields and user_template_unlocks for premium templates';
    }

    public function up(Schema $schema): void
    {
        if (!$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform) {
            return;
        }

        $this->addSql('ALTER TABLE templates ADD preview_url VARCHAR(2048) DEFAULT NULL');
        $this->addSql('ALTER TABLE templates ADD bundle_ref VARCHAR(500) DEFAULT NULL');
        $this->addSql('ALTER TABLE templates ADD definition_json JSON DEFAULT NULL COMMENT \'(DC2Type:json)\'');

        $this->addSql("CREATE TABLE user_template_unlocks (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, template_id INT NOT NULL, unlocked_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', payment_reference VARCHAR(191) DEFAULT NULL, INDEX IDX_UTU_USER (user_id), INDEX IDX_UTU_TEMPLATE (template_id), UNIQUE INDEX UNIQ_UTU_USER_TEMPLATE (user_id, template_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql('ALTER TABLE user_template_unlocks ADD CONSTRAINT FK_UTU_USER FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_template_unlocks ADD CONSTRAINT FK_UTU_TEMPLATE FOREIGN KEY (template_id) REFERENCES templates (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        if (!$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform) {
            return;
        }

        $this->addSql('ALTER TABLE user_template_unlocks DROP FOREIGN KEY FK_UTU_USER');
        $this->addSql('ALTER TABLE user_template_unlocks DROP FOREIGN KEY FK_UTU_TEMPLATE');
        $this->addSql('DROP TABLE user_template_unlocks');
        $this->addSql('ALTER TABLE templates DROP preview_url, DROP bundle_ref, DROP definition_json');
    }
}
