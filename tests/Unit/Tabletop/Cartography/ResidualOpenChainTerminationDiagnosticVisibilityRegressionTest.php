<?php

declare(strict_types=1);

namespace GreatMarketRealm\Tabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class ResidualOpenChainTerminationDiagnosticVisibilityRegressionTest extends TestCase
{
    private function source(): string
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/js/tabletop.js');
        self::assertIsString($source);
        return $source;
    }

    public function test_audit_reports_records_first_and_distinguishes_missing_records(): void
    {
        $source = $this->source();
        self::assertStringContainsString('G.5Z.22A termination audit:', $source);
        self::assertStringContainsString("'MISSING'", $source);
        self::assertStringContainsString('diagnostic records unavailable', $source);
        self::assertStringContainsString('reconstructedSurfaceOpenChainTerminations', $source);
    }

    public function test_markers_render_after_review_strokes_and_clear_with_layer(): void
    {
        $source = $this->source();
        $render = substr($source, strpos($source, 'const renderCartographySuggestions = () => {'), strpos($source, 'const updateCartographyDraftControls = () => {') - strpos($source, 'const renderCartographySuggestions = () => {'));
        self::assertIsString($render);
        self::assertLessThan(strpos($render, 'gmrt-cartography-termination-marker'), strpos($render, 'cartographySuggestions.forEach((suggestion) => {'));
        self::assertStringContainsString("marker.setAttribute('data-audit-termination', String(record.id));", $render);
        self::assertStringContainsString("marker.setAttribute('pointer-events', 'none');", $render);
        self::assertStringContainsString('cartographySuggestionLayer.replaceChildren();', $render);
        self::assertStringContainsString("cartographyDetail?.value === 'audit'", $render);
    }
}
