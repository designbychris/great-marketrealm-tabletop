<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Integration\Companion;

final class WordPressCompanionCharacterGateway implements CompanionCharacterGateway
{
    public function available(): bool
    {
        return function_exists('gmrc')
            || class_exists('GreatMarketrealmCompanion\\Core\\Application', false);
    }

    public function version(): ?string
    {
        return defined('GMRC_VERSION') ? (string) GMRC_VERSION : null;
    }

    public function charactersForUser(int $userId): array
    {
        if ($userId < 1 || ! function_exists('apply_filters')) {
            return [];
        }
        $characters = apply_filters('gmrc_tabletop_owned_characters', [], $userId);
        return is_array($characters) ? array_values(array_filter($characters, 'is_array')) : [];
    }

    public function characterForUser(int $userId, string $characterId): ?array
    {
        if ($userId < 1 || trim($characterId) === '' || ! function_exists('apply_filters')) {
            return null;
        }
        $character = apply_filters('gmrc_tabletop_owned_character', null, $userId, trim($characterId));
        return is_array($character) ? $character : null;
    }
}
