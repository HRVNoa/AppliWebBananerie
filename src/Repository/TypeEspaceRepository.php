<?php

namespace App\Repository;

use App\Entity\TypeEspace;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TypeEspace>
 *
 * @method TypeEspace|null find($id, $lockMode = null, $lockVersion = null)
 * @method TypeEspace|null findOneBy(array $criteria, array $orderBy = null)
 * @method TypeEspace[]    findAll()
 * @method TypeEspace[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TypeEspaceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeEspace::class);
    }

//    /**
//     * @return TypeEspace[] Returns an array of TypeEspace objects
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

//    public function findOneBySomeField($value): ?TypeEspace
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
