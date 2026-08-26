<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Tokens\Services;

use GreatMarketrealmTabletop\Tables\Tokens\Contracts\TableTokenIdGenerator;

defined('ABSPATH') || exit;

final class UuidTableTokenIdGenerator implements TableTokenIdGenerator
{
    public function generate(): string
    {
        if (function_exists('wp_generate_uuid4')) {
            return wp_generate_uuid4();
        }

        return bin2hex(random_bytes(16));
    }
}
