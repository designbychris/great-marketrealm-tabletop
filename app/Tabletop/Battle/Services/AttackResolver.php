<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Services;

use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\D20Roller;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\AttackOutcome;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\AttackRollMode;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\CombatProfile;

defined('ABSPATH') || exit;

final class AttackResolver
{
    public function __construct(
        private D20Roller $roller
    ) {}

    public function resolve(
        CombatProfile $attacker,
        CombatProfile $target,
        string $rollMode = AttackRollMode::NORMAL
    ): AttackOutcome {
        $rollMode = AttackRollMode::assert(
            $rollMode
        );

        $rolls = [$this->roller->roll()];

        if ($rollMode !== AttackRollMode::NORMAL) {
            $rolls[] = $this->roller->roll();
        }

        $roll = match ($rollMode) {
            AttackRollMode::ADVANTAGE => max($rolls),
            AttackRollMode::DISADVANTAGE => min($rolls),
            default => $rolls[0],
        };

        return new AttackOutcome(
            $roll,
            $attacker->attackModifier(),
            $target->armorClass(),
            $rollMode,
            $rolls
        );
    }
}
