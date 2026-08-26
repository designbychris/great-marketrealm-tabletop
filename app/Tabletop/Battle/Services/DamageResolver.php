<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Services;

use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\DamageDieRoller;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\DamageProfile;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\DamageRoll;

defined('ABSPATH') || exit;

final class DamageResolver
{
    public function __construct(
        private DamageDieRoller $roller
    ) {}

    public function resolve(
        DamageProfile $profile,
        bool $critical = false
    ): DamageRoll {
        $diceCount = $profile->diceCount()
            * ($critical ? 2 : 1);

        $rolls = [];

        for ($index = 0; $index < $diceCount; ++$index) {
            $rolls[] = $this->roller->roll(
                $profile->dieSides()
            );
        }

        return new DamageRoll(
            $rolls,
            $profile->modifier(),
            $critical
        );
    }
}
