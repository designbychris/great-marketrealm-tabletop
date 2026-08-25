<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Integration\Companion;

defined('ABSPATH') || exit;

interface CompanionGateway
{
    public function available(): bool;

    public function version(): ?string;
}
