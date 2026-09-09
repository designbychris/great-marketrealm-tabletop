<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Encounters;

use PHPUnit\Framework\TestCase;

final class BossEncounterBoundaryRegressionTest extends TestCase
{
    public function test_forged_boss_reuses_the_existing_encounter_boundary(): void
    {
        $root = dirname(__DIR__, 4);
        $manager = file_get_contents($root . '/app/Tabletop/Encounters/Services/EncounterManager.php');
        $factory = file_get_contents($root . '/app/Tabletop/Encounters/Services/EncounterManagerFactory.php');
        $view = file_get_contents($root . '/app/Tabletop/Views/chamber.php');

        self::assertIsString($manager);
        self::assertStringContainsString('awakenIfParticipating(', $manager);
        self::assertStringContainsString('new LairEncounterParticipant(', (string) $factory);
        self::assertStringContainsString('lair_occupant_token_id', (string) $view);
        self::assertStringContainsString('Boss Lair occupant', (string) $view);
        self::assertStringContainsString('ordinary Turn of Battle', (string) $view);
    }
}
