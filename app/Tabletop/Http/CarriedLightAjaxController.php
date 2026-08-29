<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tabletop\Http;
use GreatMarketrealmTabletop\Tables\Memberships\Contracts\TableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMemberStatus;
use GreatMarketrealmTabletop\Tables\Scenes\Contracts\TableSceneRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Contracts\TableTokenRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableTokenType;
use GreatMarketrealmTabletop\Tabletop\Light\Contracts\CarriedLightRepository;
use Throwable;
defined('ABSPATH') || exit;
final class CarriedLightAjaxController
{
    public function __construct(private TableMembershipRepository $members, private TableSceneRepository $scenes, private TableTokenRepository $tokens, private CarriedLightRepository $lights) {}
    public function toggle(): void
    {
        if (!is_user_logged_in()) wp_send_json_error(['message'=>'Authentication required.'],401);
        check_ajax_referer(TabletopAjaxController::NONCE_ACTION,'nonce');
        try {
            $tableId=sanitize_text_field((string)($_POST['table_id']??''));
            $userId=get_current_user_id();
            $member=$this->members->find($tableId,$userId);
            if ($member===null || $member->status()!==TableMemberStatus::ACTIVE) throw new \RuntimeException('Only an active Table member may tend a lantern.');
            $scene=null; foreach ($this->scenes->forTable($tableId) as $candidate) { if ($candidate->isActive()) { $scene=$candidate; break; } }
            if ($scene===null) throw new \RuntimeException('There is no active Chamber for this lantern.');
            $token=null; foreach ($this->tokens->forScene($tableId,$scene->id()) as $candidate) {
                if ($candidate->type()===TableTokenType::CHARACTER && $candidate->controllerUserId()===$userId) { $token=$candidate; break; }
            }
            if ($token===null) throw new \RuntimeException('Bring your Companion adventurer to the Table before lighting a torch.');
            $lit=!$this->lights->isLit($tableId,$scene->id(),$token->id());
            $this->lights->setLit($tableId,$scene->id(),$token->id(),$lit);
            wp_send_json_success(['lit'=>$lit,'token_id'=>$token->id(),'range_feet'=>40,'message'=>$lit?'The First Lantern burns: 20 ft bright light, then 20 ft dim light.':'The lantern is doused.']);
        } catch (Throwable $e) { wp_send_json_error(['message'=>$e->getMessage()],400); }
    }
}
