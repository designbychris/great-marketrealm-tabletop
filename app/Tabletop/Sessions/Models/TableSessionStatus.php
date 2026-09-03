<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Sessions\Models;

defined('ABSPATH') || exit;

final class TableSessionStatus
{
    public const ACTIVE = 'active';
    public const ENDED = 'ended';

    private function __construct() {}
}
