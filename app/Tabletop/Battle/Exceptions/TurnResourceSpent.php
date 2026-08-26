<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Exceptions;

use DomainException;

defined('ABSPATH') || exit;

final class TurnResourceSpent extends DomainException
{
}
