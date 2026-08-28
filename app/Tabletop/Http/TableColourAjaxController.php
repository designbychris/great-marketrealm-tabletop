<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Http;

use GreatMarketrealmTabletop\Tables\Memberships\Contracts\TableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMemberStatus;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableColourPalette;
use Throwable;

final class TableColourAjaxController
{
    public function __construct(private TableMembershipRepository $members) {}
    public function choose(): void
    {
        if (! is_user_logged_in()) wp_send_json_error(['message'=>'Authentication required.'],401);
        check_ajax_referer(TabletopAjaxController::NONCE_ACTION,'nonce');
        try {
            $tableId=sanitize_text_field((string)($_POST['table_id']??''));
            $colour=sanitize_key((string)($_POST['colour']??''));
            if (! TableColourPalette::has($colour)) throw new \RuntimeException('Choose a colour from the Great Marketrealm Table Palette.');
            $member=$this->members->find($tableId,get_current_user_id());
            if ($member===null || $member->status()!==TableMemberStatus::ACTIVE) throw new \RuntimeException('Only an active Table member may choose a Fellowship colour.');
            $collision = false;
            foreach ($this->members->forTable($tableId) as $other) {
                if ($other->userId() !== $member->userId() && $other->isActive() && $other->tableColour() === $colour) { $collision = true; break; }
            }
            $member->chooseTableColour($colour); $this->members->save($member);
            wp_send_json_success(['colour'=>$member->tableColour(),'hex'=>TableColourPalette::hex($member->tableColour()),'collision'=>$collision,'message'=>$collision ? 'Fellowship colour chosen — another adventurer also bears this ribbon.' : 'Fellowship colour chosen.']);
        } catch (Throwable $e) { wp_send_json_error(['message'=>$e->getMessage()],400); }
    }
}
