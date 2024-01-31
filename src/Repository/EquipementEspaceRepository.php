<?php

namespace App\Repository;

use App\Entity\EquipementEspace;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EquipementEspace>
 *
 * @method EquipementEspace|null find($id, $lockMode = null, $lockVersion = null)
 * @method EquipementEspace|null findOneBy(array $criteria, array $orderBy = null)
 * @method EquipementEspace[]    findAll()
 * @method EquipementEspace[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EquipementEspaceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EquipementEspace::class);
    }

//    /**
//     * @return EquipementEspace[] Returns an array of EquipementEspace objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?EquipementEspace
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
