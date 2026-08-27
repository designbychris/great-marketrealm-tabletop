<?php
declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Cartography\Services;

use GreatMarketrealmTabletop\Tabletop\Cartography\Exceptions\CartographyDenied;
use GreatMarketrealmTabletop\Tabletop\Cartography\Models\BattlemapImage;

defined('ABSPATH') || exit;

final class BattlemapInspector
{
    public function inspect(int $attachmentId): BattlemapImage
    {
        if ($attachmentId < 1 || ! wp_attachment_is_image($attachmentId)) {
            throw new CartographyDenied(
                'Choose an image from the WordPress Media Library for this battlemap.'
            );
        }

        $metadata = wp_get_attachment_metadata($attachmentId);
        $url = wp_get_attachment_image_url($attachmentId, 'full');

        $width = is_array($metadata)
            ? (int) ($metadata['width'] ?? 0)
            : 0;
        $height = is_array($metadata)
            ? (int) ($metadata['height'] ?? 0)
            : 0;

        if (
            $width < 1
            || $height < 1
            || ! is_string($url)
            || $url === ''
        ) {
            throw new CartographyDenied(
                'The selected battlemap image has no usable dimensions.'
            );
        }

        return new BattlemapImage(
            $attachmentId,
            $width,
            $height,
            $url
        );
    }
}
