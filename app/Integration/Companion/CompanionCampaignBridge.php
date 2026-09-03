<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Integration\Companion;

use GreatMarketrealmTabletop\Tabletop\Sessions\Models\TableSession;
use GreatMarketrealmTabletop\Tabletop\Sessions\Repositories\WordPressSessionRecapRepository;

final class CompanionCampaignBridge
{
    /** @return array<int,array<string,mixed>> */
    public function campaignsForKeeper(int $userId): array
    {
        if ($userId < 1) {
            return [];
        }
        $records = apply_filters('gmrt_companion_campaign_choices', [], $userId);
        return is_array($records) ? array_values(array_filter($records, 'is_array')) : [];
    }

    /** @return array<string,mixed>|null */
    public function linkedCampaign(string $tableId, int $userId): ?array
    {
        $record = apply_filters('gmrt_companion_campaign_for_table', null, $tableId, $userId);
        return is_array($record) ? $record : null;
    }

    /** @return array<string,mixed> */
    public function link(string $tableId, string $campaignId, int $userId): array
    {
        $result = apply_filters(
            'gmrt_companion_link_campaign',
            ['available' => false, 'linked' => false],
            $tableId,
            $campaignId,
            $userId
        );
        return is_array($result) ? $result : ['available' => false, 'linked' => false];
    }

    /** @return array<string,mixed> */
    public function synchronise(TableSession $session, int $userId): array
    {
        $record = $session->toArray();
        $recap = (new WordPressSessionRecapRepository())->find($session->tableId(), $session->id());
        if ($recap !== null) {
            $record['recap'] = $recap->draft();
            $record['contributions'] = $recap->contributions();
        }
        $result = apply_filters(
            'gmrt_companion_sync_table_session',
            ['available' => false, 'synchronised' => false],
            $record,
            $userId
        );
        return is_array($result) ? $result : ['available' => false, 'synchronised' => false];
    }
}
