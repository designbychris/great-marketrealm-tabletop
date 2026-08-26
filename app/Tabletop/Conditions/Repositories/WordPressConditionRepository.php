<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Conditions\Repositories;

use GreatMarketrealmTabletop\Tabletop\Conditions\Contracts\ConditionRepository;
use GreatMarketrealmTabletop\Tabletop\Conditions\Models\ConditionType;
use GreatMarketrealmTabletop\Tabletop\Conditions\Models\TokenCondition;

defined('ABSPATH') || exit;

final class WordPressConditionRepository implements ConditionRepository
{
    private const OPTION = 'gmrt_token_conditions';

    public function forToken(string $tableId, string $tokenId): array
    {
        $conditions = [];

        foreach (
            $this->records()[$tableId][$tokenId] ?? []
            as $record
        ) {
            if (is_array($record)) {
                $conditions[] = TokenCondition::reconstitute(
                    $record
                );
            }
        }

        return $conditions;
    }

    public function save(
        string $tableId,
        TokenCondition $condition
    ): void {
        $records = $this->records();
        $tokenId = $condition->tokenId();
        $key = $condition->condition();

        $records[$tableId][$tokenId][$key]
            = $condition->toArray();

        update_option(self::OPTION, $records, false);
    }

    public function remove(
        string $tableId,
        string $tokenId,
        string $condition
    ): void {
        ConditionType::assert($condition);
        $records = $this->records();

        unset($records[$tableId][$tokenId][$condition]);

        update_option(self::OPTION, $records, false);
    }

    /** @return array<string,mixed> */
    private function records(): array
    {
        $records = get_option(self::OPTION, []);

        return is_array($records) ? $records : [];
    }
}
