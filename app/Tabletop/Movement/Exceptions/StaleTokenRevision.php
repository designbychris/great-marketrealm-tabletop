<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Movement\Exceptions;

use RuntimeException;

defined('ABSPATH') || exit;

final class StaleTokenRevision extends RuntimeException
{
}
