<?php

namespace App\Repository;

use App\Entity\Career;
use App\Entity\Tenant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Career>
 */
class CareerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Career::class);
    }

    public function countByTenant(Tenant $tenant): int
    {
        return (int) $this->createQueryBuilder('c')
            ->join('c.area', 'a')
            ->select('COUNT(c.id)')
            ->where('a.tenant = :tenant')
            ->setParameter('tenant', $tenant)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countActiveByTenant(Tenant $tenant): int
    {
        return (int) $this->createQueryBuilder('c')
            ->join('c.area', 'a')
            ->select('COUNT(c.id)')
            ->where('a.tenant = :tenant')
            ->andWhere('c.active = true')
            ->setParameter('tenant', $tenant)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
