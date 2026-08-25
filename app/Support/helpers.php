<?php

declare(strict_types=1);

use GreatMarketrealmTabletop\Core\Application;

defined('ABSPATH') || exit;

if (! function_exists('gmrt')) {
    function gmrt(): Application
    {
        return Application::instance();
    }
}
