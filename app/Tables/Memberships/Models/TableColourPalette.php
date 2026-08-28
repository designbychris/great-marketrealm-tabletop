<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Memberships\Models;

defined('ABSPATH') || exit;

final class TableColourPalette
{
    private const COLOURS = [
        'aubergine' => ['label' => 'Aubergine', 'hex' => '#9b72cf'],
        'golden-cheddar' => ['label' => 'Golden Cheddar', 'hex' => '#d8ad4f'],
        'berry-preserve' => ['label' => 'Berry Preserve', 'hex' => '#c76583'],
        'market-teal' => ['label' => 'Market Teal', 'hex' => '#65b9ae'],
        'carrot-ember' => ['label' => 'Carrot Ember', 'hex' => '#d77a45'],
        'herb-green' => ['label' => 'Herb Green', 'hex' => '#7fa55b'],
        'frostberry' => ['label' => 'Frostberry', 'hex' => '#77a9d4'],
        'plum' => ['label' => 'Plum', 'hex' => '#b06aa2'],
    ];

    public static function all(): array { return self::COLOURS; }
    public static function has(string $key): bool { return isset(self::COLOURS[$key]); }
    public static function hex(string $key): string { return self::COLOURS[$key]['hex'] ?? self::COLOURS['market-teal']['hex']; }
    public static function label(string $key): string { return self::COLOURS[$key]['label'] ?? self::COLOURS['market-teal']['label']; }
    public static function defaultFor(string $tableId, int $userId): string {
        $keys=array_keys(self::COLOURS); $hash=(int)sprintf('%u', crc32($tableId . ':' . $userId)); return $keys[$hash % count($keys)];
    }
    public static function resolve(?string $key, string $tableId, int $userId): string {
        $key=trim((string)$key); return self::has($key) ? $key : self::defaultFor($tableId,$userId);
    }
}
