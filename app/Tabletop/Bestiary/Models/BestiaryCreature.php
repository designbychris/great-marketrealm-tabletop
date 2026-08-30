<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Bestiary\Models;

use InvalidArgumentException;

defined('ABSPATH') || exit;

/**
 * Immutable Bestiary definition. This is a catalogue entry, never a battlefield
 * token instance. IV.29B will deliberately cross that boundary through a
 * deployment service rather than teaching definitions about Scenes or tokens.
 */
final class BestiaryCreature
{
    /**
     * @param array<int,array<string,mixed>> $attacks
     * @param array<int,string> $resistances
     * @param array<int,string> $immunities
     * @param array<int,string> $weaknesses
     * @param array<int,string> $traits
     * @param array<string,int> $abilityScores
     * @param array<string,int> $savingThrows
     * @param array<int,string> $senses
     */
    public function __construct(
        private string $id,
        private string $name,
        private string $kind,
        private string $size,
        private int $armorClass,
        private int $hitPoints,
        private int $speedFeet,
        private array $attacks = [],
        private array $resistances = [],
        private array $immunities = [],
        private array $weaknesses = [],
        private array $traits = [],
        private array $abilityScores = [],
        private array $savingThrows = [],
        private array $senses = [],
        private string $source = 'gmrt-bestiary'
    ) {
        if (trim($id) === '' || trim($name) === '') {
            throw new InvalidArgumentException('A Bestiary creature requires an ID and name.');
        }
        if ($armorClass < 1 || $hitPoints < 1 || $speedFeet < 0) {
            throw new InvalidArgumentException('A Bestiary creature requires valid battlefield measures.');
        }
    }

    public function id(): string { return $this->id; }
    public function name(): string { return $this->name; }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'kind' => $this->kind,
            'size' => $this->size,
            'armor_class' => $this->armorClass,
            'hit_points' => $this->hitPoints,
            'speed_feet' => $this->speedFeet,
            'attacks' => $this->attacks,
            'resistances' => array_values($this->resistances),
            'immunities' => array_values($this->immunities),
            'weaknesses' => array_values($this->weaknesses),
            'traits' => array_values($this->traits),
            'ability_scores' => $this->abilityScores,
            'saving_throws' => $this->savingThrows,
            'senses' => array_values($this->senses),
            'source' => $this->source,
        ];
    }
}
