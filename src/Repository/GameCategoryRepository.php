<?php

namespace App\Repository;

use App\Entity\GameCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GameCategory>
 */
class GameCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GameCategory::class);
    }

    // fonction pour récupérer tous les ApiId
    public function findAllApiId(): array
    {
        return $this->createQueryBuilder('gc')
            ->select('gc.apiId')
            ->orderBy('gc.apiId', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findCategoryNameByApiId(string $apiId)
    {
        return $this->createQueryBuilder('gc')
            ->andWhere('gc.apiId = :apiId')
            ->setParameter('apiId', $apiId)
            ->getQuery()
            ->getScalarResult();
    }

    public function getAllCategoriesName(): array
    {
        return $this->createQueryBuilder('gc')
            ->select('gc.apiId, gc.name')
            ->getQuery()
            ->getResult();
    }
    //    /**
    //     * @return GameCategory[] Returns an array of GameCategory objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('g')
    //            ->andWhere('g.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('g.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?GameCategory
    //    {
    //        return $this->createQueryBuilder('g')
    //            ->andWhere('g.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }


}
