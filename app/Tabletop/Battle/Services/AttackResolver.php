<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Services;

use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\D20Roller;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\AttackOutcome;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\CombatProfile;

defined('ABSPATH') || exit;

final class AttackResolver
{
    public function __construct(
        private D20Roller $roller
    ) {}

    public function resolve(
        CombatProfile $attacker,
        CombatProfile $target
    ): AttackOutcome {
        return new AttackOutcome(
            $this->roller->roll(),
            $attacker->attackModifier(),
            $target->armorClass()
        );
    }
}
