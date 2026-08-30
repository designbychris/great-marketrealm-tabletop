<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Bestiary\Repositories;

use GreatMarketrealmTabletop\Tabletop\Bestiary\Contracts\BestiaryRepository;
use GreatMarketrealmTabletop\Tabletop\Bestiary\Models\BestiaryCreature;

defined('ABSPATH') || exit;

/**
 * First Keeper's Bestiary shelf.
 *
 * These definitions deliberately mirror the combat-certified Training Grounds
 * fixtures instead of inventing a second set of combat numbers. Richer lore,
 * ability scores, saves and senses can be added as canonical creature records
 * become available without changing the Bestiary boundary.
 */
final class TrainingBestiaryRepository implements BestiaryRepository
{
    /** @return array<int,BestiaryCreature> */
    public function all(): array
    {
        return [
            new BestiaryCreature(
                'training-slime', 'Training Slime', 'ooze', 'Medium', 11, 18, 30,
                [
                    $this->attack('slam', 'Slime Slam', 'natural', 3, 5, 5, 1, 6, 1, 'bludgeoning'),
                    $this->attack('toxic-spit', 'Toxic Spit', 'ranged-weapon', 3, 20, 40, 1, 4, 1, 'poison', ['ranged']),
                ],
                ['slashing'], [], [], ['Amorphous training ooze.'], [], [], [],
                'gmrt-test:training-slime'
            ),
            new BestiaryCreature(
                'frosty-cheese-thing', 'Frosty Cheese Thing', 'dairy-creature', 'Medium', 12, 22, 30,
                [
                    $this->attack('chill-bite', 'Chill Bite', 'natural', 4, 5, 5, 1, 6, 2, 'cold'),
                    $this->attack('frost-shard', 'Frost Shard', 'ranged-weapon', 4, 30, 60, 1, 6, 2, 'cold', ['ranged']),
                ],
                [], [], ['fire'], ['Cold-bodied training creature.'], [], [], [],
                'gmrt-test:frosty-cheese'
            ),
            new BestiaryCreature(
                'suspicious-training-dummy', 'Suspicious Training Dummy', 'construct', 'Medium', 13, 30, 0,
                [
                    $this->attack('wooden-fist', 'Wooden Fist', 'improvised', 2, 5, 5, 1, 4, 0, 'bludgeoning', ['improvised']),
                    $this->attack('ember-pop', 'Ember Pop', 'ranged-weapon', 2, 30, 60, 1, 4, 0, 'fire', ['ranged']),
                ],
                [], ['poison'], [], ['Immobile training construct.'], [], [], [],
                'gmrt-test:suspicious-dummy'
            ),
        ];
    }

    public function find(string $id): ?BestiaryCreature
    {
        foreach ($this->all() as $creature) {
            if ($creature->id() === $id) {
                return $creature;
            }
        }
        return null;
    }

    /** @return array<string,mixed> */
    private function attack(
        string $id,
        string $name,
        string $kind,
        int $attackModifier,
        int $rangeFeet,
        int $longRangeFeet,
        int $diceCount,
        int $dieSides,
        int $damageModifier,
        string $damageType,
        array $properties = []
    ): array {
        return [
            'id' => $id,
            'name' => $name,
            'kind' => $kind,
            'attack_modifier' => $attackModifier,
            'range_feet' => $rangeFeet,
            'long_range_feet' => $longRangeFeet,
            'damage' => [
                'dice_count' => $diceCount,
                'die_sides' => $dieSides,
                'modifier' => $damageModifier,
                'type' => $damageType,
            ],
            'properties' => array_values($properties),
        ];
    }
}
