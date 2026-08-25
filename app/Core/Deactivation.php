<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Core;

defined('ABSPATH') || exit;

final class Deactivation
{
    public static function deactivate(): void
    {
        do_action('gmrt_deactivated');
    }
}
