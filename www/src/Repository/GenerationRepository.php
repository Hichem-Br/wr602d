<?php

namespace App\Repository;

use App\Entity\Generation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Generation>
 */
class GenerationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Generation::class);
    }

    public function countGenerationsByUserAndDateRange(\App\Entity\User $user, \DateTimeImmutable $start, \DateTimeImmutable $end): int
    {
        return $this->createQueryBuilder('g')
            ->select('count(g.id)')
            ->where('g.user = :user')
            ->andWhere('g.createdAt >= :start')
            ->andWhere('g.createdAt < :end')
            ->setParameter('user', $user)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countPdfGeneratedByUserOnDate($userId, $startOfDay, $endOfDay)
    {
        return $this->createQueryBuilder('g')
            ->select('COUNT(g.id)')
            ->where('g.user = :userId')
            ->andWhere('g.createdAt BETWEEN :startOfDay AND :endOfDay')
            ->setParameter('userId', $userId)
            ->setParameter('startOfDay', $startOfDay)
            ->setParameter('endOfDay', $endOfDay)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
