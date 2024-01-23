<?php

namespace App\Repository;

use App\Entity\Independant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Independant>
 *
 * @method Independant|null find($id, $lockMode = null, $lockVersion = null)
 * @method Independant|null findOneBy(array $criteria, array $orderBy = null)
 * @method Independant[]    findAll()
 * @method Independant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IndependantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Independant::class);
    }

//    /**
//     * @return Independant[] Returns an array of Independant objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('i.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Independant
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
