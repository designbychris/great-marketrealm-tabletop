<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tabletop\Http;
use GreatMarketrealmTabletop\Tables\Memberships\Contracts\TableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMemberStatus;
use GreatMarketrealmTabletop\Tables\Scenes\Contracts\TableSceneRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Contracts\TableTokenRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableTokenType;
use GreatMarketrealmTabletop\Tabletop\Light\Contracts\CarriedLightRepository;
use GreatMarketrealmTabletop\Tabletop\Light\Contracts\DroppedLightRepository;
use GreatMarketrealmTabletop\Tabletop\Light\Models\DroppedLight;
use Throwable;
defined('ABSPATH') || exit;
final class DroppedLightAjaxController
{
    public function __construct(private TableMembershipRepository $members,private TableSceneRepository $scenes,private TableTokenRepository $tokens,private CarriedLightRepository $carried,private DroppedLightRepository $dropped){}
    public function tend(): void {
        if(!is_user_logged_in())wp_send_json_error(['message'=>'Authentication required.'],401); check_ajax_referer(TabletopAjaxController::NONCE_ACTION,'nonce');
        try {
            $tableId=sanitize_text_field((string)($_POST['table_id']??'')); $action=sanitize_key((string)($_POST['light_action']??'')); $userId=get_current_user_id();
            $member=$this->members->find($tableId,$userId); if($member===null||$member->status()!==TableMemberStatus::ACTIVE)throw new \RuntimeException('Only an active Table member may tend a torch.');
            $scene=null; foreach($this->scenes->forTable($tableId) as $candidate)if($candidate->isActive()){$scene=$candidate;break;} if($scene===null)throw new \RuntimeException('There is no active Chamber.');
            $token=null; foreach($this->tokens->forScene($tableId,$scene->id()) as $candidate)if($candidate->type()===TableTokenType::CHARACTER&&$candidate->controllerUserId()===$userId){$token=$candidate;break;} if($token===null)throw new \RuntimeException('Bring your Companion adventurer to the Table first.');
            if($action==='drop') {
                if(!$this->carried->isLit($tableId,$scene->id(),$token->id()))throw new \RuntimeException('Light the torch before dropping it.');
                $id='torch-'.str_replace('.','-',uniqid('',true)); $this->dropped->save(new DroppedLight($id,$tableId,$scene->id(),$userId,$token->x(),$token->y())); $this->carried->setLit($tableId,$scene->id(),$token->id(),false);
                wp_send_json_success(['message'=>'THUNK. The torch burns upon the floor.','carried'=>false]);
            }
            if($action==='pickup') {
                $nearest=null;$distance=INF; foreach($this->dropped->forScene($tableId,$scene->id()) as $light){$d=hypot($light->x()-$token->x(),$light->y()-$token->y());if($d<$distance){$distance=$d;$nearest=$light;}}
                $threshold=max(0.02,($scene->gridSize()/max(1,$scene->width()))*1.5); if($nearest===null||$distance>$threshold)throw new \RuntimeException('There is no dropped torch close enough to pick up.');
                $this->dropped->delete($tableId,$scene->id(),$nearest->id()); $this->carried->setLit($tableId,$scene->id(),$token->id(),true); wp_send_json_success(['message'=>'Torch recovered. Try not to drop it somewhere alarming.','carried'=>true]);
            }
            throw new \RuntimeException('Unknown torch action.');
        } catch(Throwable $e){wp_send_json_error(['message'=>$e->getMessage()],400);}
    }
}
