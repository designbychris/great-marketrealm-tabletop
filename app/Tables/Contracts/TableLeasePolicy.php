<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Contracts;

defined('ABSPATH') || exit;

interface TableLeasePolicy
{
    public function leaseSeconds(): int;

    public function heartbeatGraceSeconds(): int;
}
