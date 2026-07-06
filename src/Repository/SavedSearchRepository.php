<?php

namespace App\Repository;

use App\Entity\SavedSearch;
use App\Entity\Tenant;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SavedSearch>
 */
class SavedSearchRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SavedSearch::class);
    }

    public function findByTenantAndUser(Tenant $tenant, User $user): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.tenant = :tenant')
            ->andWhere('s.user = :user')
            ->setParameter('tenant', $tenant)
            ->setParameter('user', $user)
            ->orderBy('s.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
