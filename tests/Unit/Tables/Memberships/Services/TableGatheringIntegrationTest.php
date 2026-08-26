<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tables\Memberships\Services;

use GreatMarketrealmTabletop\Core\Application;
use GreatMarketrealmTabletop\Tables\Memberships\Services\TableGathering;
use PHPUnit\Framework\TestCase;

final class TableGatheringIntegrationTest extends TestCase
{
    public function testApplicationExposesGatheringBoundary(): void
    {
        self::assertInstanceOf(
            TableGathering::class,
            Application::instance()->gathering()
        );
    }

    public function testCompanionCharacterIsStoredOnlyAsReference(): void
    {
        $source = file_get_contents(
            dirname(__DIR__, 5)
                . '/app/Tables/Memberships/Models/TableMember.php'
        );

        self::assertIsString($source);
        self::assertStringContainsString(
            'companionCharacterId',
            $source
        );
        self::assertStringNotContainsString(
            'GreatMarketrealmCompanion\\',
            $source
        );
    }
}
