<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Campaigns;

defined('ABSPATH') || exit;

final class WordPressDungeonMasterPolicy
{
    public function mayCreate(int $userId): bool
    {
        if ($userId < 1) {
            return false;
        }

        $user = get_userdata($userId);
        $roles = is_object($user) && is_array($user->roles ?? null) ? $user->roles : [];
        $roleAllows = false;

        foreach ($roles as $role) {
            $role = strtolower((string) $role);
            if ($role === 'administrator' || $role === 'dm' || str_contains($role, 'dungeon_master') || str_contains($role, 'dungeon-master') || str_contains($role, 'gmrc_dm')) {
                $roleAllows = true;
                break;
            }
        }

        return (bool) apply_filters('gmrt_tabletop_may_create_table', $roleAllows, $userId);
    }
}
