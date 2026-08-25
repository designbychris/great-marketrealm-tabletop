<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Integration\Companion;

use GreatMarketrealmTabletop\Integration\Companion\CompanionAvailability;
use GreatMarketrealmTabletop\Integration\Companion\CompanionGateway;
use PHPUnit\Framework\TestCase;

final class CompanionAvailabilityTest extends TestCase
{
    public function testDetectorImplementsStableGateway(): void
    {
        self::assertInstanceOf(
            CompanionGateway::class,
            new CompanionAvailability()
        );
    }

    public function testDetectorReturnsABoolean(): void
    {
        self::assertIsBool(
            (new CompanionAvailability())->available()
        );
    }

    public function testVersionProbeIsSafe(): void
    {
        $version = (new CompanionAvailability())->version();

        self::assertTrue(
            $version === null || is_string($version)
        );
    }
}
