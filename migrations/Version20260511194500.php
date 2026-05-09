<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260511194500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'payment_transactions (+ gateway/status/purpose enums as varchar) + templates.premium_price';
    }

    public function up(Schema $schema): void
    {
        if (!$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform) {
            return;
        }

        $this->addSql('ALTER TABLE templates ADD premium_price NUMERIC(10, 2) DEFAULT NULL');

        $this->addSql("CREATE TABLE payment_transactions (
            id INT AUTO_INCREMENT NOT NULL,
            public_id VARCHAR(36) NOT NULL,
            user_id INT NOT NULL,
            gateway VARCHAR(24) NOT NULL,
            status VARCHAR(32) NOT NULL,
            purpose VARCHAR(40) NOT NULL,
            amount NUMERIC(12, 2) NOT NULL,
            currency VARCHAR(3) NOT NULL,
            related_template_id INT DEFAULT NULL,
            gateway_preference_id VARCHAR(64) DEFAULT NULL,
            gateway_payment_id VARCHAR(64) DEFAULT NULL,
            metadata JSON DEFAULT NULL COMMENT '(DC2Type:json)',
            created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
            paid_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
            updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
            failure_reason VARCHAR(500) DEFAULT NULL,
            UNIQUE INDEX UNIQ_PAYMENTS_PUBLIC_ID (public_id),
            INDEX IDX_PAYMENTS_USER (user_id),
            INDEX IDX_PAYMENTS_STATUS (status),
            INDEX IDX_PAYMENTS_GATEWAY_PAYMENT_ID (gateway_payment_id),
            PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");

        $this->addSql('ALTER TABLE payment_transactions ADD CONSTRAINT FK_PAYMENTS_USER FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE payment_transactions ADD CONSTRAINT FK_PAYMENTS_TEMPLATE FOREIGN KEY (related_template_id) REFERENCES templates (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        if (!$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform) {
            return;
        }

        $this->addSql('ALTER TABLE payment_transactions DROP FOREIGN KEY FK_PAYMENTS_USER');
        $this->addSql('ALTER TABLE payment_transactions DROP FOREIGN KEY FK_PAYMENTS_TEMPLATE');
        $this->addSql('DROP TABLE payment_transactions');
        $this->addSql('ALTER TABLE templates DROP premium_price');
    }
}
