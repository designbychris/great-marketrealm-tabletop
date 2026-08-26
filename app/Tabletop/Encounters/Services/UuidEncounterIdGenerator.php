<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Encounters\Services;

use GreatMarketrealmTabletop\Tabletop\Encounters\Contracts\EncounterIdGenerator;

defined('ABSPATH') || exit;

final class UuidEncounterIdGenerator implements EncounterIdGenerator
{
    public function generate(): string
    {
        if (function_exists('wp_generate_uuid4')) {
            return wp_generate_uuid4();
        }

        return bin2hex(random_bytes(16));
    }
}
