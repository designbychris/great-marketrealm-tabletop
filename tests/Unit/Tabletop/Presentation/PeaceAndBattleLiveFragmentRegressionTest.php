<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presentation;

use PHPUnit\Framework\TestCase;

final class PeaceAndBattleLiveFragmentRegressionTest extends TestCase
{
    private string $controller;
    private string $provider;
    private string $script;

    protected function setUp(): void
    {
        $root = dirname(__DIR__, 4);
        $this->controller = (string) file_get_contents(
            $root . '/app/Tabletop/Http/TabletopAjaxController.php'
        );
        $this->provider = (string) file_get_contents(
            $root . '/app/Tabletop/TabletopServiceProvider.php'
        );
        $this->script = (string) file_get_contents(
            $root . '/assets/js/tabletop.js'
        );
    }

    public function testLiveFragmentHasAuthenticatedAjaxRoute(): void
    {
        self::assertStringContainsString(
            "'wp_ajax_gmrt_tabletop_fragment'",
            $this->provider
        );
        self::assertStringContainsString(
            "[\$this->ajax, 'fragment']",
            $this->provider
        );
    }

    public function testLiveFragmentIsRenderedForCurrentViewer(): void
    {
        self::assertStringContainsString('public function fragment(): void', $this->controller);
        self::assertStringContainsString('get_current_user_id()', $this->controller);
        self::assertStringContainsString("'html' => \$this->renderer->render(\$state)", $this->controller);
    }

    public function testLifecycleDoesNotFetchCacheableFrontendPage(): void
    {
        $start = strpos($this->script, 'async function replaceLifecycle(message)');
        $end = strpos($this->script, 'const prepareTestTableButton', $start ?: 0);
        self::assertNotFalse($start);
        self::assertNotFalse($end);
        $fragment = substr($this->script, (int) $start, (int) $end - (int) $start);
        self::assertStringContainsString("request('gmrt_tabletop_fragment', {})", $fragment);
        self::assertStringNotContainsString("fetch(window.location.href", $fragment);
    }

    public function testLifecycleKeepsBattlefieldDomAlive(): void
    {
        self::assertStringContainsString('currentLifecycle.replaceChildren', $this->script);
        self::assertStringContainsString('currentLogSlot.replaceChildren', $this->script);
        self::assertStringNotContainsString('currentLifecycle.replaceWith(incomingLifecycle)', $this->script);
    }
}
