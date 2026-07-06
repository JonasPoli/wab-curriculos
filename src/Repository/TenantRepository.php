<?php

namespace App\Repository;

use App\Entity\Tenant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tenant>
 */
class TenantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tenant::class);
    }

    public function findByDomain(string $domain): ?Tenant
    {
        $cleanDomain = preg_replace('/^(https?:\/\/)?(www\.)?/', '', strtolower($domain));
        $cleanDomain = rtrim($cleanDomain, '/');

        $tenants = $this->createQueryBuilder('t')
            ->where('t.domain LIKE :domain')
            ->setParameter('domain', '%' . $cleanDomain . '%')
            ->getQuery()
            ->getResult();

        foreach ($tenants as $tenant) {
            $dbDomain = preg_replace('/^(https?:\/\/)?(www\.)?/', '', strtolower($tenant->getDomain()));
            $dbDomain = rtrim($dbDomain, '/');
            if ($dbDomain === $cleanDomain) {
                return $tenant;
            }
        }

        return null;
    }
}
