<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Exceptions;

use RuntimeException;

defined('ABSPATH') || exit;

final class TableLeaseExpired extends RuntimeException
{
}
