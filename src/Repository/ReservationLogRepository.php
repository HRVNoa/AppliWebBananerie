<?php

namespace App\Repository;

use App\Entity\ReservationLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ReservationLog>
 *
 * @method ReservationLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method ReservationLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method ReservationLog[]    findAll()
 * @method ReservationLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReservationLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReservationLog::class);
    }

//    /**
//     * @return ReservationLog[] Returns an array of ReservationLog objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ReservationLog
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
