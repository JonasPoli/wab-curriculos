<?php

namespace App\Service;

use App\Entity\Tenant;

class TenantContext
{
    private ?Tenant $tenant = null;

    public function getTenant(): ?Tenant
    {
        return $this->tenant;
    }

    public function setTenant(?Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    public function hasTenant(): bool
    {
        return $this->tenant !== null;
    }

    public function requireTenant(): Tenant
    {
        if ($this->tenant === null) {
            throw new \LogicException('No tenant loaded for this request.');
        }
        return $this->tenant;
    }
}
