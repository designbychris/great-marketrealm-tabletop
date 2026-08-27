<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Memberships\Contracts;

defined('ABSPATH') || exit;

interface TableMemberIdentityDirectory
{
    /** @return array{user_id:int,display_name:string,avatar_url:string} */
    public function forUser(int $userId): array;

    public function resolve(string $identifier): ?int;
}
