<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Models;

defined('ABSPATH') || exit;

final class DamageAdjustment
{
    /**
     * @param array<int,string> $effects
     */
    public function __construct(
        private string $damageType,
        private int $rawDamage,
        private int $resolvedDamage,
        private array $effects = []
    ) {}

    public function resolvedDamage(): int
    {
        return $this->resolvedDamage;
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return [
            'damage_type' => $this->damageType,
            'raw_damage' => $this->rawDamage,
            'resolved_damage' => $this->resolvedDamage,
            'effects' => $this->effects,
        ];
    }
}
