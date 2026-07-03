<?php

namespace App\Tests\Twig;

use App\Twig\AppExtension;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the AppExtension Twig extension.
 */
class AppExtensionTest extends TestCase
{
    private AppExtension $extension;

    protected function setUp(): void
    {
        $this->extension = new AppExtension();
    }

    // ── badge() ──────────────────────────────────────────────────────────────

    public function testBadgeRendersHtml(): void
    {
        $html = $this->extension->badge('Ativo');

        $this->assertStringContainsString('Ativo', $html);
        $this->assertStringContainsString('<span', $html);
        $this->assertStringContainsString('rounded-full', $html); // pill by default
    }

    public function testBadgeSuccessType(): void
    {
        $html = $this->extension->badge('OK', 'success');

        $this->assertStringContainsString('emerald', $html);
    }

    public function testBadgeDangerType(): void
    {
        $html = $this->extension->badge('Erro', 'danger');

        $this->assertStringContainsString('red', $html);
    }

    public function testBadgeWarningType(): void
    {
        $html = $this->extension->badge('Atenção', 'warning');

        $this->assertStringContainsString('amber', $html);
    }

    public function testBadgeInfoType(): void
    {
        $html = $this->extension->badge('Info', 'info');

        $this->assertStringContainsString('blue', $html);
    }

    public function testBadgeNeutralTypeByDefault(): void
    {
        $html = $this->extension->badge('Neutro');

        $this->assertStringContainsString('slate', $html);
    }

    public function testBadgeEscapesHtmlCharacters(): void
    {
        $html = $this->extension->badge('<script>alert(1)</script>', 'success');

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    public function testBadgeNoPillShape(): void
    {
        $html = $this->extension->badge('Tag', 'neutral', false);

        $this->assertStringNotContainsString('rounded-full', $html);
        $this->assertStringContainsString('rounded', $html);
    }

    // ── roleLabel() ──────────────────────────────────────────────────────────

    public function testRoleLabelForAdmin(): void
    {
        $this->assertSame('Administrador', $this->extension->roleLabel('ROLE_ADMIN'));
    }

    public function testRoleLabelForUser(): void
    {
        $this->assertSame('Usuário', $this->extension->roleLabel('ROLE_USER'));
    }

    public function testRoleLabelForUnknownRole(): void
    {
        $label = $this->extension->roleLabel('ROLE_EDITOR');

        $this->assertNotEmpty($label);
        $this->assertStringNotContainsString('ROLE_', $label);
    }

    // ── Extension Registration ───────────────────────────────────────────────

    public function testExtensionRegistersFunctions(): void
    {
        $functionNames = array_map(
            fn($f) => $f->getName(),
            $this->extension->getFunctions()
        );

        $this->assertContains('active_class', $functionNames);
        $this->assertContains('badge', $functionNames);
    }

    public function testExtensionRegistersFilters(): void
    {
        $filterNames = array_map(
            fn($f) => $f->getName(),
            $this->extension->getFilters()
        );

        $this->assertContains('badge', $filterNames);
        $this->assertContains('role_label', $filterNames);
    }
}
