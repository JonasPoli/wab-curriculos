<?php

namespace App\Controller\Admin;

use App\Entity\Tenant;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/tenants')]
#[IsGranted('ROLE_SUPER_ADMIN')]
final class TenantImpersonationController extends AbstractController
{
    #[Route('/{id}/impersonar', name: 'admin_tenant_impersonate', methods: ['GET'])]
    public function enter(Tenant $tenant, Request $request): Response
    {
        $request->getSession()->set('admin_impersonate_tenant_id', $tenant->getId());
        $request->getSession()->set('admin_impersonate_tenant_name', $tenant->getName());

        $this->addFlash('info', sprintf('Você está gerenciando o tenant <strong>%s</strong>.', htmlspecialchars($tenant->getName())));

        return $this->redirectToRoute('admin_dash');
    }

    #[Route('/sair-impersonacao', name: 'admin_tenant_exit_impersonate', methods: ['GET'])]
    public function exit(Request $request): Response
    {
        $request->getSession()->remove('admin_impersonate_tenant_id');
        $request->getSession()->remove('admin_impersonate_tenant_name');

        return $this->redirectToRoute('admin_tenant_index');
    }
}
