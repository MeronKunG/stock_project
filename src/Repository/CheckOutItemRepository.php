<?php

namespace App\Repository;

use App\Entity\CheckOutItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CheckOutItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method CheckOutItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method CheckOutItem[]    findAll()
 * @method CheckOutItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CheckOutItemRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CheckOutItem::class);
    }

    public function findByCheckOutCode($value)
    {
        return $this->createQueryBuilder('c')
            ->where('c.checkOutCode = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }
}
