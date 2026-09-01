<?php

declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tabletop\Http;
use GreatMarketrealmTabletop\Tables\Memberships\Contracts\TableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMemberStatus;
use GreatMarketrealmTabletop\Tables\Scenes\Contracts\TableSceneRepository;
use GreatMarketrealmTabletop\Tabletop\Light\Contracts\EnvironmentalLightRepository;
use GreatMarketrealmTabletop\Tabletop\Light\Models\EnvironmentalLight;
use RuntimeException; use Throwable;
defined('ABSPATH') || exit;
final class EnvironmentalLightAjaxController
{
    private const PRESETS=['torch'=>['Torch',20,20],'lantern'=>['Lantern',30,30],'brazier'=>['Brazier',30,30],'candle'=>['Candle',5,5],'magical'=>['Magical Light',20,20]];
    public function __construct(private TableMembershipRepository $members,private TableSceneRepository $scenes,private EnvironmentalLightRepository $lights) {}
    public function tend(): void {
        if(!is_user_logged_in())wp_send_json_error(['message'=>'Authentication required.'],401); check_ajax_referer(TabletopAjaxController::NONCE_ACTION,'nonce');
        try {
            $tableId=sanitize_text_field((string)($_POST['table_id']??''));$sceneId=sanitize_text_field((string)($_POST['scene_id']??''));$action=sanitize_key((string)($_POST['light_action']??''));$userId=get_current_user_id();
            $member=$this->members->find($tableId,$userId);if($member===null||$member->status()!==TableMemberStatus::ACTIVE||!$member->isDungeonMaster())throw new RuntimeException("Only the Dungeon Master may tend the Keeper's Lantern Rack.");
            if($sceneId===''){foreach($this->scenes->forTable($tableId) as $candidate)if($candidate->isActive()){$sceneId=$candidate->id();break;}}
            $scene=$sceneId!==''?$this->scenes->find($tableId,$sceneId):null;if($scene===null)throw new RuntimeException('Choose a Scene before tending its lights.');
            if($action==='place'){ $kind=sanitize_key((string)($_POST['kind']??'torch'));if(!isset(self::PRESETS[$kind]))throw new RuntimeException('That light source is not on the Lantern Rack.');[$label,$bright,$dim]=self::PRESETS[$kind];$x=max(0,min(1,(float)($_POST['x']??0)));$y=max(0,min(1,(float)($_POST['y']??0)));$id='keeper-light-'.str_replace('.','-',uniqid('',true));$light=new EnvironmentalLight($id,$tableId,$sceneId,$kind,$label,$x,$y,$bright,$dim,true);$this->lights->save($light);wp_send_json_success(['message'=>$label.' placed and burning.','light'=>$light->toArray()]); }
            $id=sanitize_text_field((string)($_POST['light_id']??''));$light=$this->lights->find($tableId,$sceneId,$id);if($light===null)throw new RuntimeException('That light source is no longer on this Scene.');
            if($action==='toggle'){ $light=$light->withLit(!$light->lit());$this->lights->save($light);wp_send_json_success(['message'=>$light->label().($light->lit()?' lit.':' extinguished.'),'light'=>$light->toArray()]); }
            if($action==='remove'){ $this->lights->delete($tableId,$sceneId,$id);wp_send_json_success(['message'=>$light->label().' returned to the Lantern Rack.','light_id'=>$id]); }
            throw new RuntimeException('Unknown Lantern Rack action.');
        } catch(Throwable $e){wp_send_json_error(['message'=>$e->getMessage()],400);}
    }
}
