<?php

namespace App\Repository;

use App\Entity\IndependantTag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IndependantTag>
 *
 * @method IndependantTag|null find($id, $lockMode = null, $lockVersion = null)
 * @method IndependantTag|null findOneBy(array $criteria, array $orderBy = null)
 * @method IndependantTag[]    findAll()
 * @method IndependantTag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IndependantTagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IndependantTag::class);
    }

//    /**
//     * @return IndependantTag[] Returns an array of IndependantTag objects
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

//    public function findOneBySomeField($value): ?IndependantTag
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
