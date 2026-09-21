<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class ResidualTerminationAuthoritativeEndpointInitializationRegressionTest extends TestCase
{
    public function test_g5z22b1_residual_authority_lookup_follows_initialized_coalesced_authority(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        $initialization = strpos($source, 'const authoritativeContourSuggestions = [];');
        $coalescence = strpos($source, 'const authoritativeReviewSlotsLiberated = Math.max(0, authoritativeInputCount - authoritativeReviewPaths);');
        $lookup = strpos($source, 'const authorityEndpointKeys = new Set(authoritativeContourSuggestions.flatMap(');
        $records = strpos($source, 'const residualTerminations = [];');
        $publication = strpos($source, '                    residualTerminations,');
        foreach ([$initialization, $coalescence, $lookup, $records, $publication] as $position) {
            self::assertNotFalse($position);
        }
        self::assertLessThan($coalescence, $initialization);
        self::assertLessThan($lookup, $coalescence);
        self::assertLessThan($records, $lookup);
        self::assertLessThan($publication, $records);
        self::assertSame(1, substr_count($source, 'const authorityEndpointKeys = new Set(authoritativeContourSuggestions.flatMap('));
        self::assertStringContainsString('const maximumReviewSuggestions = 200;', $source);
    }
}
