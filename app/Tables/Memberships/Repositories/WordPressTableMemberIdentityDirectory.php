<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Memberships\Repositories;

use GreatMarketrealmTabletop\Tables\Memberships\Contracts\TableMemberIdentityDirectory;

defined('ABSPATH') || exit;

final class WordPressTableMemberIdentityDirectory implements TableMemberIdentityDirectory
{
    public function forUser(int $userId): array
    {
        $displayName = 'User #' . $userId;

        if (function_exists('get_userdata')) {
            $user = get_userdata($userId);

            if (is_object($user)) {
                $candidate = trim((string) ($user->display_name ?? ''));
                if ($candidate !== '') {
                    $displayName = $candidate;
                }
            }
        }

        $avatarUrl = '';
        if (function_exists('get_avatar_url')) {
            $avatar = get_avatar_url($userId, ['size' => 64]);
            $avatarUrl = is_string($avatar) ? $avatar : '';
        }

        return [
            'user_id' => $userId,
            'display_name' => $displayName,
            'avatar_url' => $avatarUrl,
        ];
    }

    public function resolve(string $identifier): ?int
    {
        $identifier = trim($identifier);

        if ($identifier === '') {
            return null;
        }

        if (ctype_digit($identifier)) {
            $userId = (int) $identifier;
            return $this->exists($userId) ? $userId : null;
        }

        if (! function_exists('get_user_by')) {
            return null;
        }

        foreach (['email', 'login'] as $field) {
            $user = get_user_by($field, $identifier);
            if (is_object($user) && (int) ($user->ID ?? 0) > 0) {
                return (int) $user->ID;
            }
        }

        return null;
    }

    private function exists(int $userId): bool
    {
        if ($userId < 1 || ! function_exists('get_userdata')) {
            return false;
        }

        return is_object(get_userdata($userId));
    }
}
