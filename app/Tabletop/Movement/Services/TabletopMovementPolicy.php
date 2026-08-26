<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Movement\Services;

use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMember;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableToken;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableTokenType;

defined('ABSPATH') || exit;

final class TabletopMovementPolicy
{
    public function mayMove(
        TableMember $member,
        TableToken $token
    ): bool {
        if (! $member->isActive()) {
            return false;
        }

        if ($member->isDungeonMaster()) {
            return true;
        }

        return $token->type()
                === TableTokenType::CHARACTER
            && $token->controllerUserId()
                === $member->userId()
            && $member->companionCharacterId()
                !== null
            && $token->sourceReference()
                === $member->companionCharacterId();
    }
}
