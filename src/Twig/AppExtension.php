<?php

namespace App\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;
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
            new TwigFunction('color_mix_php', [$this, 'colorMix']),
            new TwigFunction('contrast_text_color', [$this, 'contrastTextColor']),
            new TwigFunction('tenant_logo_url', [$this, 'tenantLogoUrl'], ['needs_environment' => true]),
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

    public function colorMix(string $hex1, string $hex2, float $percent): string
    {
        $normalize = function(string $color): array {
            $color = str_replace('#', '', $color);
            if (strlen($color) === 3) {
                $color = $color[0] . $color[0] . $color[1] . $color[1] . $color[2] . $color[2];
            }
            if (strlen($color) !== 6) {
                $color = '000000';
            }
            return [
                hexdec(substr($color, 0, 2)),
                hexdec(substr($color, 2, 2)),
                hexdec(substr($color, 4, 2))
            ];
        };

        [$r1, $g1, $b1] = $normalize($hex1);
        [$r2, $g2, $b2] = $normalize($hex2);

        $weight = $percent / 100.0;

        $r = (int)round($r1 * (1.0 - $weight) + $r2 * $weight);
        $g = (int)round($g1 * (1.0 - $weight) + $g2 * $weight);
        $b = (int)round($b1 * (1.0 - $weight) + $b2 * $weight);

        $r = max(0, min(255, $r));
        $g = max(0, min(255, $g));
        $b = max(0, min(255, $b));

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }

    public function contrastTextColor(string $hex): string
    {
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        if (strlen($hex) !== 6) {
            return '#ffffff';
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;

        return ($yiq >= 180) ? '#0f172a' : '#ffffff';
    }

    public function tenantLogoUrl(Environment $env, ?string $logo, string $type = 'logo'): string
    {
        if (!$logo) {
            return '';
        }

        $path = 'uploads/tenants/' . $type . '/' . $logo;

        if (strtolower(pathinfo($logo, PATHINFO_EXTENSION)) === 'svg') {
            return '/' . $path;
        }

        $callable = $env->getFilter('imagine_filter')->getCallable();
        if (is_array($callable) && is_string($callable[0]) && class_exists($callable[0])) {
            $lazyFilterRuntime = $env->getRuntime($callable[0]);
            return call_user_func([$lazyFilterRuntime, $callable[1]], $path, 'tenant_logo');
        }

        return call_user_func($callable, $path, 'tenant_logo');
    }
}
