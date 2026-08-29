<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tabletop\Vision\Services;
use GreatMarketrealmTabletop\Tables\Memberships\Contracts\TableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMemberRole;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMemberStatus;
use GreatMarketrealmTabletop\Tables\Scenes\Contracts\TableSceneRepository;
use GreatMarketrealmTabletop\Tables\Scenes\Models\TableScene;
use GreatMarketrealmTabletop\Tables\Tokens\Contracts\TableTokenRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableTokenType;
use GreatMarketrealmTabletop\Tabletop\Fog\Contracts\FogOfWarRepository;
use GreatMarketrealmTabletop\Tabletop\Fog\Services\FogCellMapper;
use GreatMarketrealmTabletop\Tabletop\Vision\Contracts\VisionBarrierRepository;
use GreatMarketrealmTabletop\Tabletop\Vision\Models\VisionBarrier;
use RuntimeException;
defined('ABSPATH') || exit;
final class VisionBarrierManager
{
    public function __construct(private VisionBarrierRepository $barriers,private TableMembershipRepository $members,private TableSceneRepository $scenes,private ?FogOfWarRepository $fog=null,private ?TableTokenRepository $tokens=null,private ?FogCellMapper $mapper=null){}
    public function add(string $tableId,int $userId,string $type,int $x1,int $y1,int $x2,int $y2,string $sceneId=""):VisionBarrier{$scene=$this->guard($tableId,$userId,$sceneId);$barrier=new VisionBarrier('vision-'.bin2hex(random_bytes(6)),$scene->id(),$type,$x1,$y1,$x2,$y2,false);$this->barriers->save($tableId,$barrier);$this->refreshExploration($tableId,$scene);return $barrier;}
    public function toggleDoor(string $tableId,int $userId,string $id,string $sceneId=""):VisionBarrier{$scene=$this->guard($tableId,$userId,$sceneId);foreach($this->barriers->forScene($tableId,$scene->id()) as $barrier){if($barrier->id()===$id){$barrier->toggleDoor();$this->barriers->save($tableId,$barrier);$this->refreshExploration($tableId,$scene);return $barrier;}}throw new RuntimeException('That vision door could not be found.');}
    public function remove(string $tableId,int $userId,string $id,string $sceneId=""):void{$scene=$this->guard($tableId,$userId,$sceneId);$this->barriers->delete($tableId,$scene->id(),$id);$this->refreshExploration($tableId,$scene);}
    private function refreshExploration(string $tableId,TableScene $scene):void{if($this->fog===null||$this->tokens===null||$this->mapper===null){return;}$state=$this->fog->forScene($tableId,$scene->id());if(!$state->enabled()){return;}$barriers=$this->barriers->forScene($tableId,$scene->id());foreach($this->tokens->forScene($tableId,$scene->id()) as $token){if($token->type()===TableTokenType::CHARACTER){$state->reveal($this->mapper->visibleAround($scene,$token,$barriers));}}$this->fog->save($tableId,$state);}
    private function guard(string $tableId,int $userId,string $sceneId=""):TableScene{$member=$this->members->find($tableId,$userId);if($member===null||$member->status()!==TableMemberStatus::ACTIVE||$member->role()!==TableMemberRole::DUNGEON_MASTER){throw new RuntimeException('Only the Dungeon Master may alter the vision layer.');}$sceneId=trim($sceneId);if($sceneId!==''){$scene=$this->scenes->find($tableId,$sceneId);if($scene!==null){return $scene;}}foreach($this->scenes->forTable($tableId) as $scene){if($scene->isActive()){return $scene;}}throw new RuntimeException('Open a Scene before altering the vision layer.');}
}
