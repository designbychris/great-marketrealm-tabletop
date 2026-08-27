<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Presentation;

defined('ABSPATH') || exit;

final class CombatantStateProjector
{
    public const HEALTHY = 'healthy';
    public const WOUNDED = 'wounded';
    public const DOWNED = 'downed';
    public const DEFEATED = 'defeated';
    public const DECEASED = 'deceased';

    /**
     * @param array<string,mixed> $token
     * @param array<string,mixed> $vitality
     * @param array<string,mixed> $deathSaves
     */
    public function project(
        array $token,
        array $vitality,
        array $deathSaves
    ): string {
        if (! empty($deathSaves['dead'])) {
            return self::DECEASED;
        }

        $current = (int) ($vitality['current_hp'] ?? 0);
        $maximum = max(
            1,
            (int) ($vitality['maximum_hp'] ?? 1)
        );

        if ($current <= 0) {
            return ($token['type'] ?? '') === 'character'
                ? self::DOWNED
                : self::DEFEATED;
        }

        if ($current < $maximum) {
            return self::WOUNDED;
        }

        return self::HEALTHY;
    }

    public function badge(string $state): string
    {
        return match ($state) {
            self::DOWNED => 'DOWN',
            self::DEFEATED => 'KO',
            self::DECEASED => 'DEAD',
            default => '',
        };
    }
}
