<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tabletop\Cartography\Services;
use GreatMarketrealmTabletop\Tabletop\Cartography\Contracts\DungeonForgeRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMember;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableToken;
defined('ABSPATH') || exit;
final class ForgeTrapTrigger
{
    public function __construct(private DungeonForgeRepository $forge){}
    /** @return array<string,mixed>|null */
    public function afterMovement(TableMember $member,TableToken $token,float $fromX,float $fromY):?array
    {
        if($member->isDungeonMaster())return null;$p=$this->forge->forScene($token->tableId(),$token->sceneId());if(!is_array($p))return null;
        foreach(is_array($p['traps']??null)?$p['traps']:[] as $i=>$trap){if(!is_array($trap)||empty($trap['armed'])||!empty($trap['triggered']))continue;
            $x=(float)($trap['x']??-10);$y=(float)($trap['y']??-10);$r=max(.004,min(.12,(float)($trap['trigger_radius']??.018)));
            if(!$this->touches($fromX,$fromY,$token->x(),$token->y(),$x,$y,$r))continue;
            $p['traps'][$i]['triggered']=true;$p['traps'][$i]['armed']=false;$p['traps'][$i]['revealed']=true;$p['traps'][$i]['triggered_by_token_id']=$token->id();$p['traps'][$i]['triggered_at']=gmdate(DATE_ATOM);$this->forge->save($token->tableId(),$token->sceneId(),$p);return$p['traps'][$i];
        }return null;
    }
    private function touches(float $ax,float $ay,float $bx,float $by,float $px,float $py,float $r):bool{$dx=$bx-$ax;$dy=$by-$ay;$l=$dx*$dx+$dy*$dy;if($l<=.0000001)return hypot($px-$ax,$py-$ay)<=$r;$t=max(0.0,min(1.0,(($px-$ax)*$dx+($py-$ay)*$dy)/$l));return hypot($px-($ax+$t*$dx),$py-($ay+$t*$dy))<=$r;}
}
