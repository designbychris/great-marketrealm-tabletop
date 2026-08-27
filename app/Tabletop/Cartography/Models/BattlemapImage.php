<?php
declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Cartography\Models;

use InvalidArgumentException;

defined('ABSPATH') || exit;

final class BattlemapImage
{
    public function __construct(
        private int $attachmentId,
        private int $width,
        private int $height,
        private string $url
    ) {
        if (
            $attachmentId < 1
            || $width < 1
            || $height < 1
            || trim($url) === ''
        ) {
            throw new InvalidArgumentException(
                'A battlemap image requires a valid attachment, size and URL.'
            );
        }
    }

    public function attachmentId(): int { return $this->attachmentId; }
    public function width(): int { return $this->width; }
    public function height(): int { return $this->height; }
    public function url(): string { return $this->url; }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return [
            'attachment_id' => $this->attachmentId,
            'width' => $this->width,
            'height' => $this->height,
            'url' => $this->url,
        ];
    }
}
