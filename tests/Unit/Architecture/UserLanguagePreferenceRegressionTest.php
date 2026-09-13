<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Architecture;

use PHPUnit\Framework\TestCase;

final class UserLanguagePreferenceRegressionTest extends TestCase
{
    public function test_tabletop_honours_companions_shared_user_language_preference(): void
    {
        $plugin = file_get_contents(dirname(__DIR__, 3) . '/great-marketrealm-tabletop.php');

        self::assertIsString($plugin);
        self::assertStringContainsString("add_filter(\n    'determine_locale'", $plugin);
        self::assertStringContainsString("get_user_meta($userId, 'gmrc_interface_locale', true)", $plugin);
        self::assertStringContainsString("['en_GB', 'nl_NL']", $plugin);
        self::assertStringContainsString("load_plugin_textdomain(\n            'great-marketrealm-tabletop'", $plugin);
    }
}
