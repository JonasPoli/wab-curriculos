<?php

namespace App\Doctrine;

use App\Contract\TenantAwareInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class TenantFilter extends SQLFilter
{
    #[\Override]
    public function addFilterConstraint(ClassMetadata $targetEntity, string $targetTableAlias): string
    {
        if (!$targetEntity->reflClass->implementsInterface(TenantAwareInterface::class)) {
            return '';
        }

        if (!$targetEntity->hasField('tenant') && !$targetEntity->hasAssociation('tenant')) {
            return '';
        }

        return sprintf('%s.tenant_id = %s', $targetTableAlias, $this->getParameter('tenant_id'));
    }
}
