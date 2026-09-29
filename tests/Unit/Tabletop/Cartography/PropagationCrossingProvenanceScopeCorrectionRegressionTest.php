<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class PropagationCrossingProvenanceScopeCorrectionRegressionTest extends TestCase
{
    public function test_g5z49a_component_membership_ledger_is_declared_in_the_component_walk_scope(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringContainsString("IV.30.1G.5Z.49A — keep the component membership ledger", $source);
        self::assertMatchesRegularExpression(
            '/const queue=\\[startKey\\];\\s*decorationRejectVisited\\.add\\(startKey\\);.*?const componentKeys=\\[\\];\\s*let componentCells=0;.*?componentKeys\\.push\\(cellKey\\);/s',
            $source
        );
    }

    public function test_g5z49a_remains_diagnostic_only_and_does_not_add_a_global_component_fallback(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        self::assertStringNotContainsString('window.componentKeys', $source);
        self::assertStringNotContainsString('globalThis.componentKeys', $source);
        self::assertStringContainsString('wallCertified:false,admittedEdges:0,restoredRuns:0,recoveryVetoes:0', $source);
        self::assertStringContainsString('const maximumReviewSuggestions = 200;', $source);
        self::assertStringContainsString('const maximumPathVertices = 256;', $source);
    }
}
