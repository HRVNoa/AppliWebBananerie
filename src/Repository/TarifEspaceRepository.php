<?php

namespace App\Repository;

use App\Entity\TarifEspace;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TarifEspace>
 *
 * @method TarifEspace|null find($id, $lockMode = null, $lockVersion = null)
 * @method TarifEspace|null findOneBy(array $criteria, array $orderBy = null)
 * @method TarifEspace[]    findAll()
 * @method TarifEspace[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TarifEspaceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TarifEspace::class);
    }

//    /**
//     * @return TarifEspace[] Returns an array of TarifEspace objects
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

//    public function findOneBySomeField($value): ?TarifEspace
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
