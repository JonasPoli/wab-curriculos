<?php

namespace App\Controller\Admin;

use App\Entity\Tenant;
use App\Form\TenantType;
use App\Repository\TenantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/tenants')]
#[IsGranted('ROLE_SUPER_ADMIN')]
final class TenantController extends AbstractController
{
    #[Route('', name: 'admin_tenant_index', methods: ['GET'])]
    public function index(TenantRepository $tenantRepository): Response
    {
        return $this->render('admin/tenant/index.html.twig', [
            'tenants' => $tenantRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_tenant_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $tenant = new Tenant();
        $form   = $this->createForm(TenantType::class, $tenant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($tenant);
            $em->flush();
            $this->addFlash('success', 'Tenant criado com sucesso.');
            return $this->redirectToRoute('admin_tenant_index');
        }

        return $this->render('admin/tenant/new.html.twig', [
            'tenant' => $tenant,
            'form'   => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_tenant_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Tenant $tenant, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(TenantType::class, $tenant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Tenant atualizado com sucesso.');
            return $this->redirectToRoute('admin_tenant_index');
        }

        return $this->render('admin/tenant/edit.html.twig', [
            'tenant' => $tenant,
            'form'   => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_tenant_delete', methods: ['POST'])]
    public function delete(Request $request, Tenant $tenant, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_tenant_' . $tenant->getId(), $request->request->get('_token'))) {
            $em->remove($tenant);
            $em->flush();
            $this->addFlash('success', 'Tenant excluído.');
        }
        return $this->redirectToRoute('admin_tenant_index');
    }
}
