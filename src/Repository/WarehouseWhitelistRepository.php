<?php

namespace App\Repository;

use App\Entity\WarehouseWhitelist;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method WarehouseWhitelist|null find($id, $lockMode = null, $lockVersion = null)
 * @method WarehouseWhitelist|null findOneBy(array $criteria, array $orderBy = null)
 * @method WarehouseWhitelist[]    findAll()
 * @method WarehouseWhitelist[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WarehouseWhitelistRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WarehouseWhitelist::class);
    }

    // /**
    //  * @return WarehouseWhitelist[] Returns an array of WarehouseWhitelist objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('w.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?WarehouseWhitelist
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
