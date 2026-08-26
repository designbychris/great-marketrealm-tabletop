<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Tokens\Repositories;

use GreatMarketrealmTabletop\Tables\Tokens\Contracts\TableTokenRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableToken;

defined('ABSPATH') || exit;

final class WordPressTableTokenRepository implements TableTokenRepository
{
    private const OPTION = 'gmrt_table_tokens';

    /** @return array<int,TableToken> */
    public function forScene(
        string $tableId,
        string $sceneId
    ): array {
        $tokens = [];

        foreach (
            $this->records()[$tableId][$sceneId]
                ?? []
            as $record
        ) {
            if (is_array($record)) {
                $tokens[] = TableToken::reconstitute(
                    $record
                );
            }
        }

        return $tokens;
    }

    public function find(
        string $tableId,
        string $tokenId
    ): ?TableToken {
        foreach (
            $this->records()[$tableId] ?? []
            as $tokens
        ) {
            if (
                is_array($tokens)
                && isset($tokens[$tokenId])
                && is_array($tokens[$tokenId])
            ) {
                return TableToken::reconstitute(
                    $tokens[$tokenId]
                );
            }
        }

        return null;
    }

    public function save(TableToken $token): void
    {
        $records = $this->records();

        /*
         * Remove an older scene copy before saving. This keeps
         * token identity unique within one Table if scene
         * transfer is added later.
         */
        foreach (
            $records[$token->tableId()] ?? []
            as $sceneId => $tokens
        ) {
            if (
                is_array($tokens)
                && isset($tokens[$token->id()])
            ) {
                unset(
                    $records[$token->tableId()]
                        [$sceneId]
                        [$token->id()]
                );
            }
        }

        $records[$token->tableId()]
            [$token->sceneId()]
            [$token->id()] = $token->toArray();

        update_option(
            self::OPTION,
            $records,
            false
        );
    }

    /**
     * @return array<string,array<string,array<string,array<string,mixed>>>>
     */
    private function records(): array
    {
        $records = get_option(
            self::OPTION,
            []
        );

        return is_array($records)
            ? $records
            : [];
    }
}
