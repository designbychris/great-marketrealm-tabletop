<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Footsteps\Services;

use GreatMarketrealmTabletop\Tabletop\Footsteps\Contracts\FootstepTrailRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableToken;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableTokenType;

defined('ABSPATH') || exit;

final class FootstepTrailRecorder
{
    private static int $sequence = 0;

    public function __construct(private FootstepTrailRepository $trails) {}

    public function movement(TableToken $token, float $fromX, float $fromY): void
    {
        if (
            $token->type() !== TableTokenType::CHARACTER
            || ($token->controllerUserId() ?? 0) < 1
        ) {
            return;
        }

        $dx = $token->x() - $fromX;
        $dy = $token->y() - $fromY;
        if (abs($dx) < 0.00001 && abs($dy) < 0.00001) {
            return;
        }

        self::$sequence++;
        $this->trails->append(
            $token->tableId(),
            $token->sceneId(),
            $token->id(),
            [
                'token_id' => $token->id(),
                'controller_user_id' => (int) $token->controllerUserId(),
                'x' => max(0.0, min(1.0, $fromX)),
                'y' => max(0.0, min(1.0, $fromY)),
                'angle' => round(rad2deg(atan2($dy, $dx)), 2),
                'sequence' => (int) round(microtime(true) * 1000000) + self::$sequence,
            ]
        );
    }
}
