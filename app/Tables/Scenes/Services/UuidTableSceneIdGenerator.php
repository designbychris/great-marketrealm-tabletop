<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Scenes\Services;

use GreatMarketrealmTabletop\Tables\Scenes\Contracts\TableSceneIdGenerator;

defined('ABSPATH') || exit;

final class UuidTableSceneIdGenerator implements TableSceneIdGenerator
{
    public function generate(): string
    {
        if (function_exists('wp_generate_uuid4')) {
            return wp_generate_uuid4();
        }

        return bin2hex(random_bytes(16));
    }
}
