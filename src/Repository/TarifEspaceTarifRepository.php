<?php

namespace App\Repository;

use App\Entity\TarifEspaceTarif;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TarifEspaceTarif>
 *
 * @method TarifEspaceTarif|null find($id, $lockMode = null, $lockVersion = null)
 * @method TarifEspaceTarif|null findOneBy(array $criteria, array $orderBy = null)
 * @method TarifEspaceTarif[]    findAll()
 * @method TarifEspaceTarif[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TarifEspaceTarifRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TarifEspaceTarif::class);
    }

//    /**
//     * @return TarifEspaceTarif[] Returns an array of TarifEspaceTarif objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?TarifEspaceTarif
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
