<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Integration\Companion;

defined('ABSPATH') || exit;

final class CompanionAvailability implements CompanionGateway
{
    public function available(): bool
    {
        return function_exists('gmrc')
            || class_exists(
                'GreatMarketrealmCompanion\\Core\\Application',
                false
            );
    }

    public function version(): ?string
    {
        if (defined('GMRC_VERSION')) {
            return (string) GMRC_VERSION;
        }

        return null;
    }
}
