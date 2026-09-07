<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\SceneObjects;

defined('ABSPATH') || exit;

/**
 * Strict SVG boundary for administrator-authored furniture sprites.
 *
 * Sprites are decorative. Scripts, foreign content, external references,
 * event handlers and CSS are never part of the accepted vocabulary.
 */
final class FurnitureSvgSanitizer
{
    private const MAX_BYTES = 262144;

    /** @var array<int,string> */
    private const ELEMENTS = [
        'svg', 'g', 'path', 'rect', 'circle', 'ellipse', 'line', 'polyline',
        'polygon', 'defs', 'linearGradient', 'radialGradient', 'stop', 'title',
        'desc',
    ];

    /** @var array<int,string> */
    private const STYLE_PROPERTIES = [
        'fill', 'fill-opacity', 'stroke', 'stroke-width', 'stroke-linecap',
        'stroke-linejoin', 'stroke-opacity', 'opacity',
    ];

    /** @var array<int,string> */
    private const ATTRIBUTES = [
        'viewBox', 'width', 'height', 'x', 'y', 'x1', 'y1', 'x2', 'y2',
        'cx', 'cy', 'r', 'rx', 'ry', 'd', 'points', 'fill', 'fill-opacity',
        'stroke', 'stroke-width', 'stroke-linecap', 'stroke-linejoin',
        'stroke-opacity', 'opacity', 'transform', 'gradientUnits', 'offset',
        'stop-color', 'stop-opacity', 'id', 'role', 'aria-label',
        'preserveAspectRatio',
    ];

    public function sanitize(string $svg): string
    {
        $svg = trim($svg);
        if ($svg === '' || strlen($svg) > self::MAX_BYTES || stripos($svg, '<svg') === false) {
            return '';
        }

        $previous = libxml_use_internal_errors(true);
        $document = new \DOMDocument();
        $loaded = $document->loadXML(
            $svg,
            LIBXML_NONET | LIBXML_NOBLANKS | LIBXML_NOERROR | LIBXML_NOWARNING
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (! $loaded || $document->documentElement === null || strtolower($document->documentElement->localName) !== 'svg') {
            return '';
        }

        $this->cleanNode($document->documentElement);

        $root = $document->documentElement;
        $root->setAttribute('aria-hidden', 'true');
        $root->setAttribute('focusable', 'false');

        $output = $document->saveXML($root);
        return is_string($output) ? trim($output) : '';
    }

    private function promoteSafeStyle(\DOMElement $node, string $style): void
    {
        foreach (explode(';', $style) as $declaration) {
            if (! str_contains($declaration, ':')) {
                continue;
            }

            [$rawProperty, $rawValue] = array_map('trim', explode(':', $declaration, 2));
            $property = strtolower($rawProperty);
            $value = trim($rawValue);

            if (
                ! in_array($property, self::STYLE_PROPERTIES, true)
                || $value === ''
                || preg_match('/(?:javascript:|data:|https?:|url\s*\(|expression\s*\()/i', $value)
            ) {
                continue;
            }

            // Paint values are intentionally conservative: ordinary colours,
            // "none", opacity decimals, line-cap/join keywords and numeric
            // stroke widths are enough for the Furniture sprite vocabulary.
            if (
                in_array($property, ['fill', 'stroke'], true)
                && ! preg_match('/^(?:none|currentColor|#[0-9a-f]{3,8}|rgb\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}\s*\))$/i', $value)
            ) {
                continue;
            }
            if (
                in_array($property, ['fill-opacity', 'stroke-opacity', 'opacity'], true)
                && ! preg_match('/^(?:0(?:\.\d+)?|1(?:\.0+)?)$/', $value)
            ) {
                continue;
            }
            if ($property === 'stroke-width' && ! preg_match('/^\d+(?:\.\d+)?(?:px)?$/i', $value)) {
                continue;
            }
            if ($property === 'stroke-linecap' && ! in_array(strtolower($value), ['butt', 'round', 'square'], true)) {
                continue;
            }
            if ($property === 'stroke-linejoin' && ! in_array(strtolower($value), ['miter', 'round', 'bevel'], true)) {
                continue;
            }

            $node->setAttribute($property, $value);
        }
    }

    private function cleanNode(\DOMNode $node): void
    {
        if ($node instanceof \DOMElement) {
            if (! in_array($node->localName, self::ELEMENTS, true)) {
                $node->parentNode?->removeChild($node);
                return;
            }

            // Inkscape and other editors commonly put harmless paint in a
            // style attribute. Preserve only the small presentation vocabulary
            // we explicitly trust, converting it to normal SVG attributes
            // before the style attribute itself is removed.
            if ($node->hasAttribute('style')) {
                $this->promoteSafeStyle($node, $node->getAttribute('style'));
            }

            $remove = [];
            foreach ($node->attributes as $attribute) {
                $name = $attribute->nodeName;
                $value = trim($attribute->nodeValue ?? '');
                if (
                    ! in_array($name, self::ATTRIBUTES, true)
                    || str_starts_with(strtolower($name), 'on')
                    || preg_match('/(?:javascript:|data:|https?:|url\s*\()/i', $value)
                ) {
                    $remove[] = $name;
                }
            }
            foreach ($remove as $name) {
                $node->removeAttribute($name);
            }
        }

        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof \DOMComment || $child instanceof \DOMProcessingInstruction) {
                $node->removeChild($child);
                continue;
            }
            if ($child instanceof \DOMElement) {
                $this->cleanNode($child);
            }
        }
    }
}
