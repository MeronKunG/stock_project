<?php

namespace App\Repository;

use App\Entity\PackingBatchInvoiceIncomplete;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\Query;

/**
 * @method PackingBatchInvoiceIncomplete|null find($id, $lockMode = null, $lockVersion = null)
 * @method PackingBatchInvoiceIncomplete|null findOneBy(array $criteria, array $orderBy = null)
 * @method PackingBatchInvoiceIncomplete[]    findAll()
 * @method PackingBatchInvoiceIncomplete[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PackingBatchInvoiceInCompleteRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PackingBatchInvoiceIncomplete::class);
    }

    public function deleteDataByPackingBatchId($packingBatchId)
    {
        $query = $this->createQueryBuilder('p')
            ->delete()
            ->where('p.packingBatchId =:packingBatchId')
            ->setParameter('packingBatchId', $packingBatchId)
            ->getQuery();

        return $query->execute();
    }

    public function findInvoiceByPackingBatchId($packingBatchId)
    {
        return $this->createQueryBuilder('p')
            ->Where('p.packingBatchId = :packingBatchId')
            ->setParameter('packingBatchId', $packingBatchId)
            ->getQuery()
            ->getResult();

    }

}
