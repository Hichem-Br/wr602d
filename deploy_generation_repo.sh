#!/bin/bash
docker exec -i symfony-web-v2 bash -c "cat > /var/www/src/Repository/GenerationRepository.php" <<'PHP_EOF'
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
PHP_EOF
