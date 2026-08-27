<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Services;

use GreatMarketrealmTabletop\Tabletop\Battle\Models\AttackRangeAssessment;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\CombatProfile;

defined('ABSPATH') || exit;

final class AttackRangeResolver
{
    public function assess(
        int $distanceFeet,
        CombatProfile $profile
    ): AttackRangeAssessment {
        $distanceFeet = max(0, $distanceFeet);

        if (
            $distanceFeet
            <= $profile->attackRangeFeet()
        ) {
            $status = AttackRangeAssessment::NORMAL;
        } elseif (
            $distanceFeet
            <= $profile->longRangeFeet()
        ) {
            $status = AttackRangeAssessment::LONG;
        } else {
            $status = AttackRangeAssessment::OUT;
        }

        return new AttackRangeAssessment(
            $distanceFeet,
            $profile->attackRangeFeet(),
            $profile->longRangeFeet(),
            $status
        );
    }
}
