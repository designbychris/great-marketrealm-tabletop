<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Services;

use GreatMarketrealmTabletop\Tabletop\Battle\Models\DamageAdjustment;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\DamageDefenseProfile;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\DamageType;

defined('ABSPATH') || exit;

final class DamageDefenseResolver
{
    public function resolve(
        int $rawDamage,
        string $damageType,
        DamageDefenseProfile $defenses
    ): DamageAdjustment {
        $damageType = DamageType::assert($damageType);
        $rawDamage = max(0, $rawDamage);

        if ($defenses->immuneTo($damageType)) {
            return new DamageAdjustment(
                $damageType,
                $rawDamage,
                0,
                ['immune']
            );
        }

        $resolved = $rawDamage;
        $effects = [];

        if ($defenses->resists($damageType)) {
            $resolved = intdiv($resolved, 2);
            $effects[] = 'resistant';
        }

        if ($defenses->vulnerableTo($damageType)) {
            $resolved *= 2;
            $effects[] = 'vulnerable';
        }

        return new DamageAdjustment(
            $damageType,
            $rawDamage,
            $resolved,
            $effects
        );
    }
}
