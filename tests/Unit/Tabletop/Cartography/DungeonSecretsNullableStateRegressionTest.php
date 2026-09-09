<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class DungeonSecretsNullableStateRegressionTest extends TestCase
{
    public function test_secret_projection_guard_respects_nullable_tabletop_state(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/app/Tabletop/Views/chamber.php');

        self::assertIsString($source);
        self::assertStringContainsString(
            'if ($state !== null && ! $state->isDungeonMaster() && $dungeonForge !== []) {',
            $source
        );
        self::assertStringNotContainsString(
            'if (! $state->isDungeonMaster() && $dungeonForge !== []) {',
            $source
        );
    }
}
