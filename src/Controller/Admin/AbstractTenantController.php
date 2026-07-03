<?php

namespace App\Controller\Admin;

use App\Entity\Tenant;
use App\Service\TenantContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;

abstract class AbstractTenantController extends AbstractController
{
    protected TenantContext $tenantContext;

    public function setTenantContext(TenantContext $tenantContext): void
    {
        $this->tenantContext = $tenantContext;
    }

    protected function requireTenant(): Tenant
    {
        $tenant = $this->tenantContext->getTenant();

        if ($tenant === null) {
            throw $this->createAccessDeniedException(
                'Esta área é exclusiva para gestão de tenant. Acesse via impersonação ou com uma conta de tenant.'
            );
        }

        return $tenant;
    }

    protected function denyIfNotTenantOwner(mixed $entity): void
    {
        if (!method_exists($entity, 'getTenant')) {
            return;
        }

        $tenant = $this->tenantContext->getTenant();
        if ($tenant === null || $entity->getTenant()?->getId() !== $tenant->getId()) {
            throw $this->createAccessDeniedException('Você não tem acesso a este recurso.');
        }
    }
}
