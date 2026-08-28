<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Footsteps\Services;

use GreatMarketrealmTabletop\Tabletop\Footsteps\Contracts\FootstepTrailRepository;
use GreatMarketrealmTabletop\Tables\Scenes\Models\TableScene;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableToken;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableTokenType;

defined('ABSPATH') || exit;

final class FootstepTrailRecorder
{
    private static int $sequence = 0;

    public function __construct(private FootstepTrailRepository $trails) {}

    public function movement(TableToken $token, TableScene $scene, float $fromX, float $fromY): void
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

        $referenceWidth = $scene->gridReferenceWidth() > 0
            ? $scene->gridReferenceWidth()
            : $scene->width();
        $referenceHeight = $scene->width() > 0
            ? $scene->height() * ($referenceWidth / $scene->width())
            : $scene->height();
        $gridSize = max(1, $scene->gridSize());

        $distancePixels = hypot($dx * $referenceWidth, $dy * $referenceHeight);
        $distanceSquares = $distancePixels / $gridSize;

        // Roughly one paired print every two 5 ft squares, capped per movement.
        $sampleCount = max(1, min(6, (int) ceil($distanceSquares / 2)));
        $angle = round(rad2deg(atan2($dy, $dx)), 2);
        $baseSequence = (int) round(microtime(true) * 1000000);

        for ($sample = 0; $sample < $sampleCount; $sample++) {
            $progress = $sample / $sampleCount;
            self::$sequence++;
            $this->trails->append(
                $token->tableId(),
                $token->sceneId(),
                $token->id(),
                [
                    'token_id' => $token->id(),
                    'controller_user_id' => (int) $token->controllerUserId(),
                    'x' => max(0.0, min(1.0, $fromX + ($dx * $progress))),
                    'y' => max(0.0, min(1.0, $fromY + ($dy * $progress))),
                    'angle' => $angle,
                    'sequence' => $baseSequence + self::$sequence,
                ]
            );
        }
    }
}
