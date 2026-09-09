<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Cartography\Services;

use GreatMarketrealmTabletop\Tabletop\Bestiary\Contracts\BestiaryRepository;
use GreatMarketrealmTabletop\Tabletop\Bestiary\Models\BestiaryCreature;

defined('ABSPATH') || exit;

/**
 * Deterministic population pass for forged dungeons.
 *
 * Architecture still comes first: this service only proposes hidden Bestiary
 * occupants for established playable rooms. The Boss Lair remains separately
 * owned by its explicit lair-occupant contract.
 */
final class ForgeOccupantPlanner
{
    public function __construct(private BestiaryRepository $bestiary) {}

    /**
     * @param array<string,mixed> $plan
     * @return array<int,array{creature_id:string,x:float,y:float,quantity:int,room_index:int}>
     */
    public function plan(array $plan): array
    {
        if (empty($plan['populate_rooms']) || ($plan['scene_type'] ?? 'dungeon') !== 'dungeon') {
            return [];
        }

        $cols = max(1, (int) ($plan['cols'] ?? 1));
        $rows = max(1, (int) ($plan['rows'] ?? 1));
        $seed = (string) ($plan['seed'] ?? 'Peppercorn-01');
        $rooms = is_array($plan['rooms'] ?? null) ? $plan['rooms'] : [];
        $bossId = trim((string) ($plan['lair_occupant_id'] ?? ''));
        $creatures = array_values(array_filter(
            $this->bestiary->all(),
            static fn (BestiaryCreature $creature): bool => $creature->id() !== $bossId
        ));
        if ($creatures === []) return [];

        usort($creatures, static fn (BestiaryCreature $a, BestiaryCreature $b): int => strcmp($a->id(), $b->id()));
        $drafts = [];
        foreach ($rooms as $roomIndex => $room) {
            if (! is_array($room) || count($drafts) >= 8) continue;
            if (($room['role'] ?? '') === 'lair' || ! empty($room['boss_lair'])) continue;
            $w = (float) ($room['w'] ?? 0); $h = (float) ($room['h'] ?? 0);
            if ($w < 4 || $h < 4 || ($w * $h) < 20) continue;

            // Not every room is occupied. The same seed always makes the same call.
            if ($this->fraction($seed, 'occupied-' . $roomIndex) >= 0.48) continue;
            $eligible = array_values(array_filter(
                $creatures,
                fn (BestiaryCreature $creature): bool => $this->fits($creature, $w, $h)
            ));
            if ($eligible === []) continue;
            $creature = $eligible[$this->index($seed, 'creature-' . $roomIndex, count($eligible))];
            $quantity = $this->quantity($creature, $w * $h, $seed, (int) $roomIndex);
            $cx = ((float) ($room['x'] ?? 0)) + ($w / 2.0);
            $cy = ((float) ($room['y'] ?? 0)) + ($h / 2.0);
            $drafts[] = [
                'creature_id' => $creature->id(),
                'x' => max(0.0, min(1.0, $cx / $cols)),
                'y' => max(0.0, min(1.0, $cy / $rows)),
                'quantity' => $quantity,
                'room_index' => (int) $roomIndex,
            ];
        }
        return $drafts;
    }

    private function fits(BestiaryCreature $creature, float $w, float $h): bool
    {
        $size = strtolower((string) ($creature->toArray()['size'] ?? 'medium'));
        $minimum = match ($size) { 'gargantuan' => 8.0, 'huge' => 7.0, 'large' => 5.0, default => 4.0 };
        return $w >= $minimum && $h >= $minimum;
    }

    private function quantity(BestiaryCreature $creature, float $area, string $seed, int $roomIndex): int
    {
        $size = strtolower((string) ($creature->toArray()['size'] ?? 'medium'));
        if (in_array($size, ['large','huge','gargantuan'], true)) return 1;
        if ($area < 36) return 1;
        return 1 + $this->index($seed, 'pack-' . $roomIndex, $area >= 64 ? 3 : 2);
    }

    private function index(string $seed, string $salt, int $count): int
    {
        if ($count <= 1) return 0;
        return (int) (hexdec(substr(hash('sha256', $seed . '|' . $salt), 0, 8)) % $count);
    }

    private function fraction(string $seed, string $salt): float
    {
        return hexdec(substr(hash('sha256', $seed . '|' . $salt), 0, 8)) / 4294967295;
    }
}
