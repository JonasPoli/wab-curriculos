<?php

namespace App\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Application-wide Twig helpers for admin and public templates.
 */
class AppExtension extends AbstractExtension
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('nav_item_class', [$this, 'navItemClass'], ['is_safe' => ['html']]),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('role_label', [$this, 'roleLabel']),
        ];
    }

    public function navItemClass(string $routePrefix): string
    {
        $currentRouteName = $this->requestStack->getCurrentRequest()?->attributes->get('_route') ?? '';
        if (strpos($currentRouteName, $routePrefix) !== false) {
            return 'class="nav-link nav-link--active" aria-current="page"';
        }
        return 'class="nav-link" ';
    }

    /**
     * Converts a Symfony role string to a human-readable label.
     *
     * Usage: {{ 'ROLE_ADMIN'|role_label }}  → 'Administrador'
     */
    public function roleLabel(string $role): string
    {
        return match ($role) {
            'ROLE_ADMIN' => 'Administrador',
            'ROLE_USER'  => 'Usuário',
            default      => ucfirst(strtolower(str_replace(['ROLE_', '_'], ['', ' '], $role))),
        };
    }
}
