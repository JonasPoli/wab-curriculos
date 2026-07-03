<?php

namespace App\Twig;

use App\Service\TenantContext;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFunction;

class TenantExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(private readonly TenantContext $tenantContext) {}

    public function getGlobals(): array
    {
        return [];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('tenant_css_vars', [$this, 'renderCssVars'], ['is_safe' => ['html']]),
            new TwigFunction('current_tenant',   [$this, 'getCurrentTenant']),
        ];
    }

    public function getCurrentTenant(): mixed
    {
        return $this->tenantContext->getTenant();
    }

    public function renderCssVars(): string
    {
        $tenant = $this->tenantContext->getTenant();
        if ($tenant === null) {
            return '';
        }

        $primary   = $tenant->getPrimaryColor() ?? '#0044cc';
        $secondary = $tenant->getSecondaryColor() ?? '#ffaa00';
        $darkPri   = $tenant->getPrimaryColorDark() ?? '#3b82f6';
        $darkSec   = $tenant->getSecondaryColorDark() ?? '#fbbf24';

        $vars = [
            '--color-primary'        => $primary,
            '--color-secondary'      => $secondary,
            '--color-primary-dark'   => $darkPri,
            '--color-secondary-dark' => $darkSec,
            '--bm-accent'            => $primary,
            '--bm-accent-dark'       => $darkPri,
        ];

        $css = ':root {' . "\n";
        foreach ($vars as $name => $value) {
            $css .= sprintf('    %s: %s;' . "\n", $name, htmlspecialchars($value, ENT_QUOTES));
        }
        $css .= '}';

        return '<style>' . "\n" . $css . "\n" . '</style>';
    }
}
