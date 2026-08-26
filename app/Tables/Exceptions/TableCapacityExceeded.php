<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Exceptions;

use RuntimeException;

defined('ABSPATH') || exit;

final class TableCapacityExceeded extends RuntimeException
{
    public static function forLimit(int $limit): self
    {
        return new self(
            sprintf(
                'The Guild currently permits %d simultaneously active table%s.',
                $limit,
                $limit === 1 ? '' : 's'
            )
        );
    }
}
