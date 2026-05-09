<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\PaymentGateway;
use App\Enum\PaymentPurpose;
use App\Enum\PaymentTransactionStatus;
use App\Repository\PaymentTransactionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaymentTransactionRepository::class)]
#[ORM\Table(name: 'payment_transactions')]
#[ORM\Index(name: 'IDX_PAYMENTS_USER', fields: ['user'])]
#[ORM\Index(name: 'IDX_PAYMENTS_STATUS', fields: ['status'])]
#[ORM\Index(name: 'IDX_PAYMENTS_GATEWAY_PAYMENT_ID', fields: ['gatewayPaymentId'])]
#[ORM\UniqueConstraint(name: 'UNIQ_PAYMENTS_PUBLIC_ID', fields: ['publicId'])]
final class PaymentTransaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 36, unique: true)]
    private string $publicId;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(length: 24, enumType: PaymentGateway::class)]
    private PaymentGateway $gateway;

    #[ORM\Column(length: 32, enumType: PaymentTransactionStatus::class)]
    private PaymentTransactionStatus $status;

    #[ORM\Column(length: 40, enumType: PaymentPurpose::class)]
    private PaymentPurpose $purpose;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    private string $amount;

    #[ORM\Column(length: 3)]
    private string $currency;

    #[ORM\ManyToOne(targetEntity: TemplateCatalogItem::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?TemplateCatalogItem $relatedTemplate = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $gatewayPreferenceId = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $gatewayPaymentId = null;

    /**
     * @var array<string, mixed>|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $metadata = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $paidAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $failureReason = null;

    public static function beginTemplateUnlock(
        string $publicId,
        User $user,
        TemplateCatalogItem $template,
        string $amount,
        string $currency = 'BRL',
    ): self {
        $e = new self();
        $e->publicId = $publicId;
        $e->user = $user;
        $e->gateway = PaymentGateway::MercadoPago;
        $e->status = PaymentTransactionStatus::Pending;
        $e->purpose = PaymentPurpose::TemplatePremiumUnlock;
        $e->amount = $amount;
        $e->currency = $currency;
        $e->relatedTemplate = $template;
        $e->createdAt = new \DateTimeImmutable();

        return $e;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPublicId(): string
    {
        return $this->publicId;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getGateway(): PaymentGateway
    {
        return $this->gateway;
    }

    public function getStatus(): PaymentTransactionStatus
    {
        return $this->status;
    }

    public function getPurpose(): PaymentPurpose
    {
        return $this->purpose;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getRelatedTemplate(): ?TemplateCatalogItem
    {
        return $this->relatedTemplate;
    }

    public function getGatewayPreferenceId(): ?string
    {
        return $this->gatewayPreferenceId;
    }

    public function getGatewayPaymentId(): ?string
    {
        return $this->gatewayPaymentId;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    /**
     * @param array<string, mixed>|null $metadata
     */
    public function mergeMetadata(?array $metadata): void
    {
        if ($metadata === null || $metadata === []) {
            return;
        }
        $base = $this->metadata ?? [];
        $this->metadata = [...$base, ...$metadata];
        $this->touch();
    }

    public function setGatewayPreferenceId(string $preferenceId): void
    {
        $this->gatewayPreferenceId = $preferenceId;
        $this->touch();
    }

    public function markProcessing(): void
    {
        $this->status = PaymentTransactionStatus::Processing;
        $this->touch();
    }

    public function markPaid(?string $gatewayPaymentId): void
    {
        $this->status = PaymentTransactionStatus::Paid;
        $this->paidAt ??= new \DateTimeImmutable();
        if ($gatewayPaymentId !== null && $gatewayPaymentId !== '') {
            $this->gatewayPaymentId = $gatewayPaymentId;
        }
        $this->touch();
    }

    public function markFailed(string $reason): void
    {
        $this->status = PaymentTransactionStatus::Failed;
        $this->failureReason = mb_substr(trim($reason), 0, 500);
        $this->touch();
    }

    public function markCancelled(): void
    {
        $this->status = PaymentTransactionStatus::Cancelled;
        $this->touch();
    }

    public function markRefunded(): void
    {
        $this->status = PaymentTransactionStatus::Refunded;
        $this->touch();
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getPaidAt(): ?\DateTimeImmutable
    {
        return $this->paidAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getFailureReason(): ?string
    {
        return $this->failureReason;
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
