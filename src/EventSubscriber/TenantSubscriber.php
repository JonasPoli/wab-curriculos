<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Repository\TenantRepository;
use App\Service\TenantContext;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class TenantSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly TenantRepository $tenantRepository,
        private readonly TenantContext $tenantContext,
        private readonly EntityManagerInterface $em,
        private readonly Security $security,
    ) {}

    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', -10],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $path    = $request->getPathInfo();

        if (str_starts_with($path, '/_')
            || str_starts_with($path, '/login')
            || str_starts_with($path, '/logout')
        ) {
            return;
        }

        if (str_starts_with($path, '/admin')) {
            $this->resolveAdminTenant($request->hasSession() ? $request->getSession() : null);
            return;
        }

        $host   = $request->getHost();
        $tenant = $this->tenantRepository->findByDomain($host);

        if ($tenant === null) {
            return;
        }

        $this->activateTenantFilter($tenant->getId());
        $this->tenantContext->setTenant($tenant);
    }

    private function resolveAdminTenant(mixed $session): void
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            return;
        }

        if ($session instanceof SessionInterface
            && $session->isStarted()
            && $session->has('admin_impersonate_tenant_id')
        ) {
            $tenantId = $session->get('admin_impersonate_tenant_id');
            $tenant   = $this->tenantRepository->find($tenantId);
            if ($tenant !== null) {
                $this->activateTenantFilter($tenant->getId());
                $this->tenantContext->setTenant($tenant);
                return;
            }
        }

        if ($user->getTenant() !== null) {
            $this->activateTenantFilter($user->getTenant()->getId());
            $this->tenantContext->setTenant($user->getTenant());
        }
    }

    private function activateTenantFilter(int $tenantId): void
    {
        $filter = $this->em->getFilters()->enable('tenant_filter');
        $filter->setParameter('tenant_id', $tenantId, 'integer');
    }
}
