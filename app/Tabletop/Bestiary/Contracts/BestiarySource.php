<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Bestiary\Contracts;

defined('ABSPATH') || exit;

/** External catalogue source. Returns neutral records; Tabletop owns conversion. */
interface BestiarySource
{
    public function available(): bool;
    public function label(): string;

    /** @return array<int,array<string,mixed>> */
    public function records(): array;
}
