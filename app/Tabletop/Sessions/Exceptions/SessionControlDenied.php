<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Sessions\Exceptions;

use RuntimeException;

defined('ABSPATH') || exit;

final class SessionControlDenied extends RuntimeException {}
