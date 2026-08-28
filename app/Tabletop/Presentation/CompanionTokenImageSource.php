<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Presentation;

defined('ABSPATH') || exit;

/**
 * Keeps Companion token artwork safe when rendered by the Tabletop Chamber.
 *
 * Generated Companion portraits arrive as base64 SVG data URIs. WordPress
 * esc_url() intentionally removes the data: scheme, so those portraits need
 * a narrowly-scoped image-only allowance while ordinary uploaded token URLs
 * continue through normal WordPress URL escaping.
 */
final class CompanionTokenImageSource
{
    public static function escaped(string $source): string
    {
        $source = trim($source);

        if ($source === '') {
            return '';
        }

        if (self::isGeneratedSvgDataUri($source)) {
            return esc_attr($source);
        }

        return esc_url($source);
    }

    private static function isGeneratedSvgDataUri(string $source): bool
    {
        $prefix = 'data:image/svg+xml;base64,';
        if (! str_starts_with($source, $prefix)) {
            return false;
        }

        $payload = substr($source, strlen($prefix));
        if ($payload === '' || preg_match('/^[A-Za-z0-9+\/]+={0,2}$/', $payload) !== 1) {
            return false;
        }

        $decoded = base64_decode($payload, true);
        if (! is_string($decoded) || $decoded === '') {
            return false;
        }

        $trimmed = ltrim($decoded);
        return str_starts_with($trimmed, '<svg')
            || str_starts_with($trimmed, '<?xml');
    }
}
