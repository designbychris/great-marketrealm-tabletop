<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Satchel\Services;

use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\D20Roller;
use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\DamageDieRoller;
use InvalidArgumentException;

final class SpellPouchRoller
{
    public function __construct(
        private D20Roller $d20,
        private DamageDieRoller $spellDice
    ) {}

    /** @param array<string,mixed> $character @return array<string,mixed> */
    public function roll(array $character, string $action, string $spellId): array
    {
        $spell = $this->spell($character, $spellId);
        $action = trim($action);
        $label = (string) ($spell['label'] ?? 'Spell');

        if ($action === 'attack') {
            if (($spell['spell_attack'] ?? null) === null) {
                throw new InvalidArgumentException('That spell does not make a spell attack roll.');
            }

            $die = $this->d20->roll();
            $modifier = (int) $spell['spell_attack'];

            return [
                'action' => 'attack',
                'spell_id' => (string) ($spell['id'] ?? ''),
                'label' => $label,
                'die' => $die,
                'modifier' => $modifier,
                'total' => $die + $modifier,
                'natural_twenty' => $die === 20,
                'natural_one' => $die === 1,
            ];
        }

        if ($action === 'damage' || $action === 'healing') {
            $rollKind = (string) ($spell['roll_kind'] ?? '');
            if ($rollKind !== $action) {
                throw new InvalidArgumentException(
                    $action === 'healing'
                        ? 'That spell does not provide a healing roll.'
                        : 'That spell does not provide a damage roll.'
                );
            }

            [$count, $sides] = $this->formula((string) ($spell['formula'] ?? ''));
            $rolls = [];
            for ($index = 0; $index < $count; $index += 1) {
                $rolls[] = $this->spellDice->roll($sides);
            }

            $modifier = (int) ($spell['roll_modifier'] ?? 0);
            $diceTotal = array_sum($rolls);

            return [
                'action' => $action,
                'spell_id' => (string) ($spell['id'] ?? ''),
                'label' => $label,
                'formula' => sprintf('%dd%d', $count, $sides),
                'rolls' => $rolls,
                'dice_total' => $diceTotal,
                'modifier' => $modifier,
                'total' => $diceTotal + $modifier,
                'damage_type' => (string) ($spell['damage_type'] ?? ''),
            ];
        }

        throw new InvalidArgumentException('That Spell Pouch action is not supported.');
    }

    /** @param array<string,mixed> $character @return array<string,mixed> */
    private function spell(array $character, string $spellId): array
    {
        $play = is_array($character['play'] ?? null) ? $character['play'] : [];
        $spellcasting = is_array($play['spellcasting'] ?? null) ? $play['spellcasting'] : [];
        $spells = is_array($spellcasting['spells'] ?? null) ? $spellcasting['spells'] : [];

        foreach ($spells as $spell) {
            if (is_array($spell) && (string) ($spell['id'] ?? '') === $spellId) {
                return $spell;
            }
        }

        throw new InvalidArgumentException('That spell is not present in this Companion Character\'s Spell Pouch.');
    }

    /** @return array{0:int,1:int} */
    private function formula(string $formula): array
    {
        if (! preg_match('/^(\d+)d(4|6|8|10|12|20|100)$/i', trim($formula), $matches)) {
            throw new InvalidArgumentException('That spell formula is not supported by the Tabletop dice boundary.');
        }

        $count = (int) $matches[1];
        if ($count < 1 || $count > 20) {
            throw new InvalidArgumentException('That spell asks for too many dice.');
        }

        return [$count, (int) $matches[2]];
    }
}
