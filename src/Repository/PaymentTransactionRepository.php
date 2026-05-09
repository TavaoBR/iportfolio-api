<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\PaymentTransaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PaymentTransaction>
 */
final class PaymentTransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaymentTransaction::class);
    }

    public function save(PaymentTransaction $transaction): PaymentTransaction
    {
        $em = $this->getEntityManager();
        $em->persist($transaction);
        $em->flush();

        return $transaction;
    }

    public function findByPublicId(string $publicId): ?PaymentTransaction
    {
        return $this->findOneBy(['publicId' => $publicId]);
    }

    public function findByMercadoPagoPreferenceId(string $preferenceId): ?PaymentTransaction
    {
        return $this->findOneBy(['gatewayPreferenceId' => $preferenceId]);
    }

    public function findByMercadoPagoPaymentId(string $paymentId): ?PaymentTransaction
    {
        return $this->findOneBy(['gatewayPaymentId' => $paymentId]);
    }
}
