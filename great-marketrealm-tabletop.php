<?php
/**
 * Plugin Name: Great Marketrealm Tabletop
 * Plugin URI:  https://greatmarketrealm.co.uk/
 * Description: The live virtual tabletop for adventures across The Great Marketrealm.
 * Version:     0.32.0-alpha.8
 * Author:      Great Marketrealm
 * Text Domain: great-marketrealm-tabletop
 * Domain Path: /languages
 * Requires PHP: 8.1
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

define('GMRT_VERSION', '0.32.0-alpha.8');
define('GMRT_FILE', __FILE__);
define('GMRT_PATH', plugin_dir_path(__FILE__));
define('GMRT_URL', plugin_dir_url(__FILE__));


/**
 * Honour the same per-user interface-language preference used by Companion.
 * This keeps locale personal to each participant at a shared Table rather than
 * making language a campaign-wide setting.
 */
add_filter(
    'determine_locale',
    static function (string $locale): string {
        if (! function_exists('get_current_user_id') || ! function_exists('get_user_meta')) {
            return $locale;
        }

        $userId = get_current_user_id();
        if ($userId < 1) {
            return $locale;
        }

        $preferred = (string) get_user_meta($userId, 'gmrc_interface_locale', true);
        if (in_array($preferred, ['en_GB', 'nl_NL'], true)) {
            return $preferred;
        }

        return $locale;
    },
    1
);

/**
 * Load Tabletop interface translations from the bundled language-pack directory.
 *
 * Locale packs translate the application chrome and controls. Canonical MarketRealm
 * rules/lore remain a separate content-translation concern so names and wordplay can
 * be curated rather than mechanically rewritten.
 */
add_action(
    'init',
    static function (): void {
        load_plugin_textdomain(
            'great-marketrealm-tabletop',
            false,
            dirname(plugin_basename(GMRT_FILE)) . '/languages'
        );
    },
    1
);

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
