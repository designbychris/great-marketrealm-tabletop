<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use GreatMarketrealmTabletop\Tabletop\Cartography\Services\ForgeTreasurePlanner;
use PHPUnit\Framework\TestCase;

final class ForgeTreasurePlannerTest extends TestCase
{
    public function test_treasure_is_opt_in(): void
    {
        self::assertSame([], (new ForgeTreasurePlanner())->plan($this->plan(false)));
    }

    public function test_treasure_is_dungeon_only(): void
    {
        $plan = $this->plan(true);
        $plan['scene_type'] = 'forest';
        self::assertSame([], (new ForgeTreasurePlanner())->plan($plan));
    }

    public function test_treasure_is_deterministic(): void
    {
        $plan = $this->plan(true);
        $planner = new ForgeTreasurePlanner();
        self::assertSame($planner->plan($plan), $planner->plan($plan));
    }

    public function test_opted_in_dungeon_with_suitable_room_gets_treasure(): void
    {
        $treasure = (new ForgeTreasurePlanner())->plan($this->plan(true));
        self::assertNotEmpty($treasure);
    }

    public function test_treasure_begins_hidden_and_unlooted(): void
    {
        $treasure = (new ForgeTreasurePlanner())->plan($this->plan(true));
        self::assertNotEmpty($treasure);
        foreach ($treasure as $record) {
            self::assertFalse($record['revealed']);
            self::assertFalse($record['looted']);
            self::assertSame('treasure', $record['kind']);
        }
    }

    public function test_genuine_boss_lair_receives_a_hoard(): void
    {
        $plan = $this->plan(true);
        $plan['style'] = 'grand';
        $plan['rooms'][] = ['x' => 18, 'y' => 10, 'w' => 9, 'h' => 9, 'role' => 'lair', 'boss_lair' => true];
        $treasure = (new ForgeTreasurePlanner())->plan($plan);
        self::assertSame('lair-hoard', $treasure[0]['treasure_type']);
        self::assertSame('Boss Hoard', $treasure[0]['label']);
    }

    private function plan(bool $include): array
    {
        return [
            'include_treasure' => $include,
            'scene_type' => 'dungeon',
            'seed' => 'Pippin-treasure',
            'style' => 'standard',
            'cols' => 30,
            'rows' => 22,
            'rooms' => [
                ['x' => 2, 'y' => 2, 'w' => 8, 'h' => 8, 'role' => '', 'boss_lair' => false],
                ['x' => 12, 'y' => 3, 'w' => 7, 'h' => 7, 'role' => '', 'boss_lair' => false],
            ],
        ];
    }
}
