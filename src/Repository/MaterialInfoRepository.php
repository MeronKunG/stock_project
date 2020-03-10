<?php

namespace App\Repository;

use App\Entity\MaterialInfo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MaterialInfo|null find($id, $lockMode = null, $lockVersion = null)
 * @method MaterialInfo|null findOneBy(array $criteria, array $orderBy = null)
 * @method MaterialInfo[]    findAll()
 * @method MaterialInfo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MaterialInfoRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MaterialInfo::class);
    }

    // /**
    //  * @return MaterialInfo[] Returns an array of MaterialInfo objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MaterialInfo
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function findByMaterialName(string $query)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.materialName LIKE :query')
            ->setParameter('query', '%'.$query.'%')
            ->getQuery()
            ->getResult();
    }
}
