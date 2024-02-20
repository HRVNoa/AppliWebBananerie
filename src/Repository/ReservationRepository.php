<?php

namespace App\Repository;

use App\Entity\Reservation;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reservation>
 *
 * @method Reservation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reservation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reservation[]    findAll()
 * @method Reservation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    public function findExistingReservation($heureDebut, $heureFin, $espace, $date)
    {
         $r = $this->createQueryBuilder('r')
            ->andWhere('r.heureDebut < :heureFin AND r.heureFin > :heureDebut and r.espace = :espace and r.date= :date')
            ->setParameter('heureFin', $heureFin)
            ->setParameter('heureDebut', $heureDebut)
            ->setParameter('espace', $espace)
            ->setParameter('date', $date)
            ->getQuery()
            ->getResult();
        return $r;
    }

    public function getReservationFutureByUser($user): array
    {
        $date= new DateTime();
        return $this->createQueryBuilder('r')
            ->andWhere('r.user = :user and r.date >= :date')
            ->setParameter('user', $user)
            ->setParameter('date', $date->format('Y/m/d'))
            ->orderBy('r.date', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

//    /**
//     * @return Reservation[] Returns an array of Reservation objects
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

//    public function findOneBySomeField($value): ?Reservation
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
