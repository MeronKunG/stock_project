<?php

namespace App\Repository;

use App\Entity\OrderReceive;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method OrderReceive|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderReceive|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderReceive[]    findAll()
 * @method OrderReceive[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderReceiveRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, OrderReceive::class);
    }

    public function findInvoice($invoice, $merchantCode)
    {
        return $this->createQueryBuilder('u')
            // ->select('u.invoice')
            ->andWhere('u.invoice = :invoice')
            ->andWhere('u.merchantCode = :merchantCode')
            ->setParameter('invoice', $invoice)
            ->setParameter('merchantCode', $merchantCode)
            // ->addGroupBy('u.materialCode')
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return OrderReceive[] Returns an array of OrderReceive objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?OrderReceive
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
