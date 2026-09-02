<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presentation;

use PHPUnit\Framework\TestCase;

final class DoorToTheTableRegressionTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function testSignedOutShortcodeRendersTheDoorInsteadOfPlainNotice(): void
    {
        $source = (string) file_get_contents($this->root . '/app/Tabletop/Presentation/TabletopShortcode.php');

        self::assertStringContainsString("'login_url' => wp_login_url(\$this->returnUrl())", $source);
        self::assertStringContainsString("'art_url' => GMRT_URL . 'assets/images/pippin-peppercorn-cartographer.png'", $source);
        self::assertStringNotContainsString('Please sign in to enter the Tabletop Chamber.', $source);
    }

    public function testLoginReturnUrlPreservesTheRequestedTableOrInvitationUrl(): void
    {
        $source = (string) file_get_contents($this->root . '/app/Tabletop/Presentation/TabletopShortcode.php');

        self::assertStringContainsString("\$_SERVER['REQUEST_URI']", $source);
        self::assertStringContainsString('wp_unslash(', $source);
        self::assertStringContainsString('home_url($requestUri)', $source);
    }

    public function testDoorHasAccessibleEntryCopyAndPippinFieldNote(): void
    {
        $view = (string) file_get_contents($this->root . '/app/Tabletop/Views/chamber.php');

        self::assertStringContainsString('class="gmrt-table-door"', $view);
        self::assertStringContainsString('Beyond This Door, the Table Awaits', $view);
        self::assertStringContainsString('Enter the Tabletop', $view);
        self::assertStringContainsString('Great Marketrealm Companion account', $view);
        self::assertStringContainsString("Pippin's note", $view);
    }

    public function testDoorUsesPackagedPippinArtworkWithoutChangingAuthenticationAuthority(): void
    {
        $view = (string) file_get_contents($this->root . '/app/Tabletop/Views/chamber.php');
        $source = (string) file_get_contents($this->root . '/app/Tabletop/Presentation/TabletopShortcode.php');

        self::assertStringContainsString('pippin-peppercorn-pixel.png', $view);
        self::assertStringContainsString('pippin-peppercorn-cartographer.png', $source);
        self::assertStringContainsString('wp_login_url(', $source);
        self::assertStringNotContainsString('wp_signon(', $source);
    }

    public function testDoorSharesPixelVocabularyAndResponsiveTreatment(): void
    {
        $css = (string) file_get_contents($this->root . '/assets/css/tabletop.css');

        self::assertStringContainsString('Phase IV.32.6 — The Door to the Table.', $css);
        self::assertStringContainsString('.gmrt-chamber:has(.gmrt-table-door)', $css);
        self::assertStringContainsString('.gmrt-table-door__enter:focus-visible', $css);
        self::assertStringContainsString('@media (max-width: 820px)', $css);
        self::assertStringContainsString('@media (prefers-reduced-motion: reduce)', $css);
    }
}
