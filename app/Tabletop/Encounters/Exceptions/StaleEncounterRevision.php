<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Encounters\Exceptions;

use RuntimeException;

defined('ABSPATH') || exit;

final class StaleEncounterRevision extends RuntimeException
{
}
