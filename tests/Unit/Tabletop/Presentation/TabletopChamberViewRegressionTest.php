<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presentation;

use PHPUnit\Framework\TestCase;

final class TabletopChamberViewRegressionTest extends TestCase
{
    private string $source;

    protected function setUp(): void
    {
        $path = dirname(__DIR__, 4)
            . '/app/Tabletop/Views/chamber.php';

        $this->source = (string) file_get_contents(
            $path
        );
    }

    public function testViewRendersActiveSceneAndTokens(): void
    {
        self::assertStringContainsString(
            'Active Scene',
            $this->source
        );
        self::assertStringContainsString(
            'gmrt-board__tokens',
            $this->source
        );
        self::assertStringContainsString(
            '--gmrt-token-x',
            $this->source
        );
        self::assertStringContainsString(
            'wp_get_attachment_image_url',
            $this->source
        );
    }

    public function testViewMakesInitialReadOnlyNatureExplicit(): void
    {
        self::assertStringContainsString(
            'This first chamber is read-only.',
            $this->source
        );
    }

    public function testViewDoesNotExposeDevelopmentPhaseLanguage(): void
    {
        self::assertStringNotContainsString(
            'Phase IV.',
            $this->source
        );
    }
}
