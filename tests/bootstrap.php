<?php

declare(strict_types=1);

define('ABSPATH', __DIR__ . '/wordpress/');

$GLOBALS['gmrt_test_actions'] = [];
$GLOBALS['gmrt_test_options'] = [];

if (! function_exists('plugin_dir_path')) {
    function plugin_dir_path(string $file): string
    {
        return rtrim(dirname($file), '/\\') . DIRECTORY_SEPARATOR;
    }
}

if (! function_exists('plugin_dir_url')) {
    function plugin_dir_url(string $file): string
    {
        return 'https://example.test/wp-content/plugins/'
            . basename(dirname($file))
            . '/';
    }
}

if (! function_exists('register_activation_hook')) {
    function register_activation_hook(string $file, callable $callback): void
    {
        $GLOBALS['gmrt_activation_hook'] = [$file, $callback];
    }
}

if (! function_exists('register_deactivation_hook')) {
    function register_deactivation_hook(string $file, callable $callback): void
    {
        $GLOBALS['gmrt_deactivation_hook'] = [$file, $callback];
    }
}

if (! function_exists('add_action')) {
    function add_action(
        string $hook,
        callable $callback,
        int $priority = 10
    ): void {
        $GLOBALS['gmrt_test_actions'][$hook][$priority][] = $callback;
    }
}

if (! function_exists('add_shortcode')) {
    function add_shortcode(
        string $tag,
        callable $callback
    ): void {
        $GLOBALS['gmrt_test_shortcodes'][$tag] = $callback;
    }
}

if (! function_exists('add_filter')) {
    function add_filter(
        string $hook,
        callable $callback,
        int $priority = 10
    ): void {
        $GLOBALS['gmrt_test_actions'][$hook][$priority][] = $callback;
    }
}

if (! function_exists('do_action')) {
    function do_action(string $hook, mixed ...$args): void
    {
        foreach ($GLOBALS['gmrt_test_actions'][$hook] ?? [] as $callbacks) {
            foreach ($callbacks as $callback) {
                $callback(...$args);
            }
        }
    }
}

if (! function_exists('get_option')) {
    function get_option(
        string $option,
        mixed $default = false
    ): mixed {
        return $GLOBALS['gmrt_test_options'][$option]['value']
            ?? $default;
    }
}

if (! function_exists('update_option')) {
    function update_option(
        string $option,
        mixed $value,
        bool $autoload = true
    ): bool {
        $GLOBALS['gmrt_test_options'][$option] = [
            'value' => $value,
            'autoload' => $autoload,
        ];

        return true;
    }
}

require_once dirname(__DIR__) . '/autoload.php';
