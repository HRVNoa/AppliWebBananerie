<?php

namespace App\Repository;

use App\Entity\SecteurActivité;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SecteurActivité>
 *
 * @method SecteurActivité|null find($id, $lockMode = null, $lockVersion = null)
 * @method SecteurActivité|null findOneBy(array $criteria, array $orderBy = null)
 * @method SecteurActivité[]    findAll()
 * @method SecteurActivité[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SecteurActivitéRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SecteurActivité::class);
    }

//    /**
//     * @return SecteurActivité[] Returns an array of SecteurActivité objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?SecteurActivité
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
