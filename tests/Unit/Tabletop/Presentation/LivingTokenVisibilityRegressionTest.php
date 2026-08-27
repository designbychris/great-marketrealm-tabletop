<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presentation;

use PHPUnit\Framework\TestCase;

final class LivingTokenVisibilityRegressionTest extends TestCase
{
    private string $script;

    protected function setUp(): void
    {
        $root = dirname(__DIR__, 4);
        $this->script = (string) file_get_contents($root . '/assets/js/tabletop.js');
    }

    public function testHeartbeatCanMaterialiseNewlyVisibleServerToken(): void
    {
        self::assertStringContainsString("if (!node && tokenLayer)", $this->script);
        self::assertStringContainsString("document.createElement('div')", $this->script);
        self::assertStringContainsString('tokenLayer.appendChild(node)', $this->script);
    }

    public function testHeartbeatCanRemoveTokenNoLongerInPlayerSafeProjection(): void
    {
        self::assertStringContainsString('const incomingTokenIds = new Set(', $this->script);
        self::assertStringContainsString('if (!incomingTokenIds.has(String(node.dataset.tokenId', $this->script);
        self::assertStringContainsString('node.remove();', $this->script);
    }

    public function testLiveTokenUsesOnlyAuthoritativeHeartbeatPayload(): void
    {
        self::assertStringContainsString('tokens.forEach((token) => {', $this->script);
        self::assertStringContainsString("token.controller_user_id || ''", $this->script);
    }
}
