<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserTemplateUnlockRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserTemplateUnlockRepository::class)]
#[ORM\Table(name: 'user_template_unlocks')]
#[ORM\UniqueConstraint(name: 'UNIQ_UTU_USER_TEMPLATE', fields: ['user', 'template'])]
#[ORM\Index(name: 'IDX_UTU_USER', fields: ['user'])]
#[ORM\Index(name: 'IDX_UTU_TEMPLATE', fields: ['template'])]
final class UserTemplateUnlock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\ManyToOne(targetEntity: TemplateCatalogItem::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private TemplateCatalogItem $template;

    #[ORM\Column]
    private \DateTimeImmutable $unlockedAt;

    #[ORM\Column(length: 191, nullable: true)]
    private ?string $paymentReference = null;

    public function __construct(User $user, TemplateCatalogItem $template, ?string $paymentReference = null)
    {
        $this->user = $user;
        $this->template = $template;
        $this->unlockedAt = new \DateTimeImmutable();
        $this->paymentReference = $paymentReference !== null ? trim($paymentReference) : null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getTemplate(): TemplateCatalogItem
    {
        return $this->template;
    }

    public function getUnlockedAt(): \DateTimeImmutable
    {
        return $this->unlockedAt;
    }

    public function getPaymentReference(): ?string
    {
        return $this->paymentReference;
    }
}
