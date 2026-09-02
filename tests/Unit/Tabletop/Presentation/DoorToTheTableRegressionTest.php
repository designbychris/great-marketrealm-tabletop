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

    public function testSignedOutShortcodeRendersFrontEndDoorLogin(): void
    {
        $source = (string) file_get_contents($this->root . '/app/Tabletop/Presentation/TabletopShortcode.php');
        $view = (string) file_get_contents($this->root . '/app/Tabletop/Views/chamber.php');

        self::assertStringContainsString("LOGIN_NONCE_ACTION = 'gmrt_tabletop_frontend_login'", $source);
        self::assertStringContainsString("'action_url' => \$this->returnUrl()", $source);
        self::assertStringContainsString("'nonce' => wp_create_nonce(self::LOGIN_NONCE_ACTION)", $source);
        self::assertStringContainsString('class="gmrt-table-door__form"', $view);
        self::assertStringContainsString('name="log"', $view);
        self::assertStringContainsString('name="pwd"', $view);
        self::assertStringNotContainsString('wp_login_url(', $source);
    }

    public function testDoorAuthenticatesThroughWordPressAuthorityWithNonceProtection(): void
    {
        $source = (string) file_get_contents($this->root . '/app/Tabletop/Presentation/TabletopShortcode.php');
        $provider = (string) file_get_contents($this->root . '/app/Tabletop/TabletopServiceProvider.php');

        self::assertStringContainsString("'template_redirect'", $provider);
        self::assertStringContainsString("[\$this->shortcode, 'handleDoorLogin']", $provider);
        self::assertStringContainsString('public function handleDoorLogin(): void', $source);
        self::assertStringContainsString('wp_verify_nonce(', $source);
        self::assertStringContainsString('wp_signon(', $source);
        self::assertStringContainsString("'user_login' => \$login", $source);
        self::assertStringContainsString("'user_password' => \$password", $source);
        self::assertStringContainsString('wp_set_current_user((int) $user->ID)', $source);
        self::assertStringContainsString('wp_safe_redirect($this->returnUrl())', $source);
    }

    public function testLoginReturnUrlPreservesRequestedTableOrInvitationUrl(): void
    {
        $source = (string) file_get_contents($this->root . '/app/Tabletop/Presentation/TabletopShortcode.php');

        self::assertStringContainsString("\$_SERVER['REQUEST_URI']", $source);
        self::assertStringContainsString('wp_unslash(', $source);
        self::assertStringContainsString('home_url($requestUri)', $source);
    }

    public function testDoorFormSupportsPasswordManagersErrorsAndRememberMe(): void
    {
        $view = (string) file_get_contents($this->root . '/app/Tabletop/Views/chamber.php');

        self::assertStringContainsString('autocomplete="username"', $view);
        self::assertStringContainsString('autocomplete="current-password"', $view);
        self::assertStringContainsString('name="rememberme"', $view);
        self::assertStringContainsString('class="gmrt-table-door__error" role="alert"', $view);
        self::assertStringContainsString('Enter the Tabletop', $view);
    }

    public function testDoorAndInvitationUseFullBleedPippinThresholdPresentation(): void
    {
        $view = (string) file_get_contents($this->root . '/app/Tabletop/Views/chamber.php');
        $css = (string) file_get_contents($this->root . '/assets/css/tabletop.css');

        self::assertStringContainsString('pippin-peppercorn-cartographer.png', $view);
        self::assertStringContainsString('class="gmrt-invitation-threshold"', $view);
        self::assertStringContainsString(".gmrt-table-door,\n.gmrt-invitation-threshold", $css);
        self::assertStringContainsString('width: 100vw;', $css);
        self::assertStringContainsString('object-fit: cover;', $css);
    }

    public function testInvitationAcceptanceRemainsSeparateFromAuthentication(): void
    {
        $view = (string) file_get_contents($this->root . '/app/Tabletop/Views/chamber.php');

        self::assertStringContainsString('Your chair is waiting', $view);
        self::assertStringContainsString('data-accept-table-invitation', $view);
        self::assertStringContainsString('Taking your seat creates your persistent Table membership', $view);
    }

    public function testDoorHasResponsiveKeyboardAndReducedMotionTreatment(): void
    {
        $css = (string) file_get_contents($this->root . '/assets/css/tabletop.css');

        self::assertStringContainsString('Phase IV.32.6A — The Door Has a Lock.', $css);
        self::assertStringContainsString('.gmrt-table-door__form input[type="text"]:focus', $css);
        self::assertStringContainsString('.gmrt-gathering-invitation button:focus-visible', $css);
        self::assertStringContainsString('@media (max-width: 820px)', $css);
        self::assertStringContainsString('@media (prefers-reduced-motion: reduce)', $css);
    }
}
