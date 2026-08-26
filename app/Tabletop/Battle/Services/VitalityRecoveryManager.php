<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Services;

use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\DeathSaveRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\VitalityRepository;

defined('ABSPATH') || exit;

final class VitalityRecoveryManager
{
    public function __construct(
        private VitalityRepository $vitality,
        private DeathSaveRepository $deathSaves
    ) {}

    /** @return array<string,mixed> */
    public function heal(
        string $tableId,
        string $tokenId,
        int $amount
    ): array {
        $vitality = $this->vitality->forToken(
            $tableId,
            $tokenId
        );
        $healed = $vitality->heal($amount);

        $state = $this->deathSaves->forToken(
            $tableId,
            $tokenId
        );

        if ($vitality->currentHp() > 0) {
            $state->reset();
            $this->deathSaves->save(
                $tableId,
                $state
            );
        }

        $this->vitality->save(
            $tableId,
            $vitality
        );

        return [
            'healed' => $healed,
            'vitality' => $vitality,
            'death_saves' => $state,
        ];
    }
}
