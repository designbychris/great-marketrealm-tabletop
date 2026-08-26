<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Routing;

defined('ABSPATH') || exit;

final class TabletopRoute
{
    public const QUERY_VAR = 'gmrt_tabletop';
    public const TABLE_VAR = 'gmrt_table';

    public function register(): void
    {
        add_rewrite_rule(
            '^tabletop/?$',
            'index.php?' . self::QUERY_VAR . '=1',
            'top'
        );

        add_rewrite_rule(
            '^tabletop/([^/]+)/?$',
            'index.php?'
                . self::QUERY_VAR
                . '=1&'
                . self::TABLE_VAR
                . '=$matches[1]',
            'top'
        );
    }

    /** @param array<int,string> $vars */
    public function queryVars(array $vars): array
    {
        $vars[] = self::QUERY_VAR;
        $vars[] = self::TABLE_VAR;

        return array_values(
            array_unique($vars)
        );
    }

    public function matches(): bool
    {
        return (string) get_query_var(
            self::QUERY_VAR,
            ''
        ) === '1';
    }

    public function tableId(): string
    {
        return sanitize_text_field(
            (string) get_query_var(
                self::TABLE_VAR,
                ''
            )
        );
    }
}
