<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Bestiary\Services;

use GreatMarketrealmTabletop\Integration\Companion\CompanionBestiarySource;
use GreatMarketrealmTabletop\Integration\Expansions\ExpansionBestiarySource;
use GreatMarketrealmTabletop\Tabletop\Bestiary\Contracts\BestiaryRepository;
use GreatMarketrealmTabletop\Tabletop\Bestiary\Repositories\MenagerieBestiaryRepository;
use GreatMarketrealmTabletop\Tabletop\Bestiary\Repositories\TrainingBestiaryRepository;

defined('ABSPATH') || exit;

final class BestiaryRepositoryFactory
{
    public static function make(
        string $tableId = '',
        int $viewerUserId = 0
    ): BestiaryRepository {
        $sources = [new CompanionBestiarySource()];

        if ($tableId !== '' && $viewerUserId > 0) {
            $sources[] = new ExpansionBestiarySource(
                $tableId,
                $viewerUserId
            );
        }

        return new MenagerieBestiaryRepository(
            new TrainingBestiaryRepository(),
            $sources,
            new ExternalBestiaryMapper()
        );
    }
}
