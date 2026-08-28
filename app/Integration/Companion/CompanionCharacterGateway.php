<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Integration\Companion;

interface CompanionCharacterGateway extends CompanionGateway
{
    /** @return array<int,array<string,mixed>> */
    public function charactersForUser(int $userId): array;

    /** @return array<string,mixed>|null */
    public function characterForUser(int $userId, string $characterId): ?array;

    /** @return array<string,mixed>|null */
    public function updateVitalMeasuresForUser(
        int $userId,
        string $characterId,
        int $currentHp,
        int $temporaryHp
    ): ?array;
}

