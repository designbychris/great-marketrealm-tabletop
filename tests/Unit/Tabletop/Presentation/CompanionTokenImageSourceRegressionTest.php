<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presentation;

use PHPUnit\Framework\TestCase;

final class CompanionTokenImageSourceRegressionTest extends TestCase
{
    private function root(string $path): string
    {
        return dirname(__DIR__, 4) . '/' . $path;
    }

    public function testGeneratedCompanionSvgDataUrisHaveDedicatedImageSourceEscaping(): void
    {
        $source = (string) file_get_contents(
            $this->root('app/Tabletop/Presentation/CompanionTokenImageSource.php')
        );

        self::assertStringContainsString("data:image/svg+xml;base64,", $source);
        self::assertStringContainsString('base64_decode($payload, true)', $source);
        self::assertStringContainsString('esc_attr($source)', $source);
        self::assertStringContainsString('esc_url($source)', $source);
    }

    public function testChamberUsesCompanionTokenImageSourceInsteadOfEscUrlDirectly(): void
    {
        $source = (string) file_get_contents(
            $this->root('app/Tabletop/Views/chamber.php')
        );

        self::assertStringContainsString('CompanionTokenImageSource::escaped($tokenImage)', $source);
        self::assertStringNotContainsString('esc_url($tokenImage)', $source);
    }
    public function testAdventurersSatchelUsesTheSupportedEscapedImageSourceApi(): void
    {
        $source = (string) file_get_contents(
            $this->root('app/Tabletop/Views/chamber.php')
        );

        self::assertStringContainsString(
            "CompanionTokenImageSource::escaped((string) (\$satchelToken['image_url'] ?? ''))",
            $source
        );
        self::assertStringNotContainsString('CompanionTokenImageSource::sanitize(', $source);
    }

}
