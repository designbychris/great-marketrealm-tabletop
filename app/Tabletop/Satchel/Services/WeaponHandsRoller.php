<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Satchel\Services;

use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\D20Roller;
use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\DamageDieRoller;
use InvalidArgumentException;

final class WeaponHandsRoller
{
    public function __construct(
        private D20Roller $d20,
        private DamageDieRoller $damageDice
    ) {}

    /** @param array<string,mixed> $character @return array<string,mixed> */
    public function roll(array $character, string $action, string $attackId): array
    {
        $attack = $this->attack($character, $attackId);
        $action = trim($action);

        if ($action === 'attack') {
            $die = $this->d20->roll();
            $modifier = (int) ($attack['attack_bonus'] ?? 0);

            return [
                'action' => 'attack',
                'attack_id' => (string) ($attack['id'] ?? ''),
                'label' => (string) ($attack['label'] ?? 'Weapon'),
                'die' => $die,
                'modifier' => $modifier,
                'total' => $die + $modifier,
                'natural_twenty' => $die === 20,
                'natural_one' => $die === 1,
                'critical_damage_die' => (string) ($attack['critical_damage_die'] ?? ''),
            ];
        }

        if ($action === 'damage') {
            [$count, $sides] = $this->formula((string) ($attack['damage_die'] ?? ''));
            $rolls = [];
            for ($index = 0; $index < $count; $index += 1) {
                $rolls[] = $this->damageDice->roll($sides);
            }
            $modifier = (int) ($attack['damage_modifier'] ?? 0);
            $diceTotal = array_sum($rolls);

            return [
                'action' => 'damage',
                'attack_id' => (string) ($attack['id'] ?? ''),
                'label' => (string) ($attack['label'] ?? 'Weapon'),
                'formula' => sprintf('%dd%d', $count, $sides),
                'rolls' => $rolls,
                'dice_total' => $diceTotal,
                'modifier' => $modifier,
                'total' => $diceTotal + $modifier,
                'damage_type' => (string) ($attack['damage_type'] ?? 'damage'),
            ];
        }

        throw new InvalidArgumentException('That Weapons to Hand action is not supported.');
    }

    /** @param array<string,mixed> $character @return array<string,mixed> */
    private function attack(array $character, string $attackId): array
    {
        $play = is_array($character['play'] ?? null) ? $character['play'] : [];
        $attacks = is_array($play['attacks'] ?? null) ? $play['attacks'] : [];

        foreach ($attacks as $attack) {
            if (is_array($attack) && (string) ($attack['id'] ?? '') === $attackId) {
                return $attack;
            }
        }

        throw new InvalidArgumentException('That weapon is not readied on this Companion Character.');
    }

    /** @return array{0:int,1:int} */
    private function formula(string $formula): array
    {
        if (! preg_match('/^(\\d+)d(4|6|8|10|12|20|100)$/i', trim($formula), $matches)) {
            throw new InvalidArgumentException('That weapon damage formula is not supported by the Tabletop dice boundary.');
        }

        $count = (int) $matches[1];
        if ($count < 1 || $count > 20) {
            throw new InvalidArgumentException('That weapon asks for too many damage dice.');
        }

        return [$count, (int) $matches[2]];
    }
}
