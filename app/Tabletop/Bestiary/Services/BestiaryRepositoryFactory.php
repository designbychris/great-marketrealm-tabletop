<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Bestiary\Services;

use GreatMarketrealmTabletop\Integration\Companion\CompanionBestiarySource;
use GreatMarketrealmTabletop\Tabletop\Bestiary\Contracts\BestiaryRepository;
use GreatMarketrealmTabletop\Tabletop\Bestiary\Repositories\MenagerieBestiaryRepository;
use GreatMarketrealmTabletop\Tabletop\Bestiary\Repositories\TrainingBestiaryRepository;

defined('ABSPATH') || exit;

final class BestiaryRepositoryFactory
{
    public static function make(): BestiaryRepository
    {
        return new MenagerieBestiaryRepository(
            new TrainingBestiaryRepository(),
            [new CompanionBestiarySource()],
            new ExternalBestiaryMapper()
        );
    }
}
