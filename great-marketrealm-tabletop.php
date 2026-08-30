<?php
/**
 * Plugin Name: Great Marketrealm Tabletop
 * Plugin URI:  https://greatmarketrealm.co.uk/
 * Description: The live virtual tabletop for adventures across The Great Marketrealm.
 * Version:     0.29.2-alpha.1
 * Author:      Great Marketrealm
 * Text Domain: great-marketrealm-tabletop
 * Requires PHP: 8.1
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

define('GMRT_VERSION', '0.29.2-alpha.1');
define('GMRT_FILE', __FILE__);
define('GMRT_PATH', plugin_dir_path(__FILE__));
define('GMRT_URL', plugin_dir_url(__FILE__));

require_once GMRT_PATH . 'autoload.php';

use GreatMarketrealmTabletop\Core\Activation;
use GreatMarketrealmTabletop\Core\Application;
use GreatMarketrealmTabletop\Core\Deactivation;

register_activation_hook(GMRT_FILE, [Activation::class, 'activate']);
register_deactivation_hook(GMRT_FILE, [Deactivation::class, 'deactivate']);

add_action(
    'plugins_loaded',
    static function (): void {
        Application::instance()->boot();
    },
    20
);
