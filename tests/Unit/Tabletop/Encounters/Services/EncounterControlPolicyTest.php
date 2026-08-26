<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Encounters\Services;

use DateTimeImmutable;
use GreatMarketrealmTabletop\Tabletop\Encounters\Services\EncounterControlPolicy;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMember;
use PHPUnit\Framework\TestCase;

final class EncounterControlPolicyTest extends TestCase
{
    public function testOnlyActiveDungeonMasterMayControlEncounter(): void
    {
        $policy = new EncounterControlPolicy();

        $dm = TableMember::dungeonMaster(
            'table-1', 42, new DateTimeImmutable()
        );

        $player = TableMember::invitePlayer(
            'table-1', 84, new DateTimeImmutable()
        );
        $player->join(new DateTimeImmutable());

        self::assertTrue($policy->mayControl($dm));
        self::assertFalse($policy->mayControl($player));
        self::assertFalse($policy->mayControl(null));
    }
}
