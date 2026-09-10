<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Cartography\Services;

defined('ABSPATH') || exit;

/**
 * Plans sparse, deterministic treasure for Dungeon Forge scenes.
 *
 * Treasure is preparation metadata rather than inventory. It stays Keeper-only
 * until revealed and can later be marked looted without inventing a second
 * character inventory/currency system inside Cartography.
 */
final class ForgeTreasurePlanner
{
    /** @param array<string,mixed> $plan @return array<int,array<string,mixed>> */
    public function plan(array $plan): array
    {
        if (($plan['scene_type'] ?? 'dungeon') !== 'dungeon' || empty($plan['include_treasure'])) {
            return [];
        }

        $rooms = is_array($plan['rooms'] ?? null) ? array_values($plan['rooms']) : [];
        $cols = max(1, (int) ($plan['cols'] ?? 1));
        $rows = max(1, (int) ($plan['rows'] ?? 1));
        $seed = (string) ($plan['seed'] ?? 'Peppercorn');
        $style = (string) ($plan['style'] ?? 'standard');
        $maximum = match ($style) {
            'compact' => 1,
            'grand' => 3,
            default => 2,
        };

        $drafts = [];

        // A genuine Boss Lair gets first refusal on a hoard. This deliberately
        // consumes one of the sparse treasure slots rather than adding clutter.
        foreach ($rooms as $index => $room) {
            if (! is_array($room) || ($room['role'] ?? '') !== 'lair' || empty($room['boss_lair'])) {
                continue;
            }
            $drafts[] = $this->draft($seed, (int) $index, $room, $cols, $rows, 'lair-hoard', 'hoard');
            break;
        }

        $eligible = [];
        foreach ($rooms as $index => $room) {
            if (count($drafts) >= $maximum || ! is_array($room)) {
                break;
            }
            if (($room['role'] ?? '') === 'lair' || ! empty($room['boss_lair'])) {
                continue;
            }
            $w = (int) ($room['w'] ?? 0);
            $h = (int) ($room['h'] ?? 0);
            if ($w < 4 || $h < 4 || ($w * $h) < 20) {
                continue;
            }
            $eligible[] = [(int) $index, $room];
            if ($this->fraction($seed, 'treasure-use-' . $index) > 0.38) {
                continue;
            }

            $type = $this->pick($seed, 'treasure-type-' . $index, [
                'coin-cache', 'trade-goods', 'adventurer-cache', 'curio-stash',
            ]);
            $tier = $style === 'grand' && $this->fraction($seed, 'treasure-tier-' . $index) < 0.25
                ? 'worthy'
                : 'modest';
            $drafts[] = $this->draft($seed, (int) $index, $room, $cols, $rows, $type, $tier);
        }

        // Opting into treasure should never silently produce an empty ledger
        // when the dungeon actually contains a suitable room.
        if ($drafts === [] && $eligible !== []) {
            [$index, $room] = $eligible[0];
            $type = $this->pick($seed, 'treasure-fallback-type-' . $index, [
                'coin-cache', 'trade-goods', 'adventurer-cache', 'curio-stash',
            ]);
            $drafts[] = $this->draft($seed, $index, $room, $cols, $rows, $type, 'modest');
        }

        return $drafts;
    }

    /** @param array<string,mixed> $room @return array<string,mixed> */
    private function draft(string $seed, int $roomIndex, array $room, int $cols, int $rows, string $type, string $tier): array
    {
        $x = (float) ($room['x'] ?? 0);
        $y = (float) ($room['y'] ?? 0);
        $w = max(2.0, (float) ($room['w'] ?? 2));
        $h = max(2.0, (float) ($room['h'] ?? 2));
        $px = $x + ($w * (0.30 + (0.40 * $this->fraction($seed, 'treasure-x-' . $roomIndex))));
        $py = $y + ($h * (0.30 + (0.40 * $this->fraction($seed, 'treasure-y-' . $roomIndex))));

        return [
            'id' => 'forge-treasure-' . substr(hash('sha256', $seed . '|treasure|' . $roomIndex . '|' . $type), 0, 18),
            'kind' => 'treasure',
            'treasure_type' => $type,
            'label' => $this->label($type),
            'contents' => $this->contents($type, $tier),
            'value_tier' => $tier,
            'x' => max(0.0, min(1.0, $px / $cols)),
            'y' => max(0.0, min(1.0, $py / $rows)),
            'room_index' => $roomIndex,
            'revealed' => false,
            'looted' => false,
            'manual' => false,
        ];
    }

    private function label(string $type): string
    {
        return match ($type) {
            'lair-hoard' => 'Boss Hoard',
            'trade-goods' => 'Trade Goods',
            'adventurer-cache' => 'Adventurer’s Cache',
            'curio-stash' => 'Curio Stash',
            default => 'Coin Cache',
        };
    }

    private function contents(string $type, string $tier): string
    {
        if ($type === 'lair-hoard') {
            return 'A substantial hoard of mixed coin, valuables, and one conspicuously important prize for the Keeper to define.';
        }
        $worthy = $tier === 'worthy';
        return match ($type) {
            'trade-goods' => $worthy
                ? 'Valuable trade goods, sealed provisions, and merchant tokens.'
                : 'A useful bundle of trade goods and saleable provisions.',
            'adventurer-cache' => $worthy
                ? 'Well-kept adventuring supplies and a notable consumable or tool.'
                : 'A small cache of adventuring supplies worth carrying onward.',
            'curio-stash' => $worthy
                ? 'Several curios, one of which looks important enough to deserve a name.'
                : 'A peculiar curio and a few saleable trinkets.',
            default => $worthy
                ? 'A respectable cache of mixed coin and stamped trade tokens.'
                : 'A modest cache of mixed coin and trade tokens.',
        };
    }

    /** @param array<int,string> $choices */
    private function pick(string $seed, string $salt, array $choices): string
    {
        $index = min(count($choices) - 1, (int) floor($this->fraction($seed, $salt) * count($choices)));
        return $choices[max(0, $index)];
    }

    private function fraction(string $seed, string $salt): float
    {
        return hexdec(substr(hash('sha256', $seed . '|' . $salt), 0, 8)) / 4294967295;
    }
}
