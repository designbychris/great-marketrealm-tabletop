<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Services;

use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\DeathSaveRoller;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\DeathSaveOutcome;

defined('ABSPATH') || exit;

final class DeathSaveResolver
{
    public function __construct(
        private DeathSaveRoller $roller
    ) {}

    public function resolve(): DeathSaveOutcome
    {
        return new DeathSaveOutcome(
            $this->roller->roll()
        );
    }
}
