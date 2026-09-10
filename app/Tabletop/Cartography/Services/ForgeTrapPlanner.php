<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tabletop\Cartography\Services;
defined('ABSPATH') || exit;
final class ForgeTrapPlanner
{
    /** @param array<string,mixed> $plan @return array<int,array<string,mixed>> */
    public function plan(array $plan):array
    {
        if(empty($plan['include_traps'])||($plan['scene_type']??'dungeon')!=='dungeon')return[];
        $seed=(string)($plan['seed']??'Peppercorn-01');$cols=max(1,(int)($plan['cols']??1));$rows=max(1,(int)($plan['rows']??1));
        $rooms=is_array($plan['rooms']??null)?array_values($plan['rooms']):[];$doors=is_array($plan['doors']??null)?array_values($plan['doors']):[];$eligible=[];
        foreach($rooms as $i=>$r){if(!is_array($r)||($r['role']??'')==='lair'||!empty($r['boss_lair'])||(float)($r['w']??0)<4||(float)($r['h']??0)<4)continue;$eligible[$i]=$r;}
        $traps=[];
        if($eligible!==[]){$keys=array_keys($eligible);$ri=$keys[$this->index($seed,'pressure',count($keys))];$r=$eligible[$ri];$traps[]=['id'=>'forge-trap-'.substr(hash('sha256',$seed.'|pressure|'.$ri),0,12),'kind'=>'trap','trap_type'=>'pressure-plate','label'=>'Pressure Plate','effect'=>'A concealed mechanism snaps into motion.','room_index'=>$ri,'x'=>((float)$r['x']+(float)$r['w']*.48)/$cols,'y'=>((float)$r['y']+(float)$r['h']*.58)/$rows,'trigger_radius'=>max(.012,.58/max($cols,$rows)),'revealed'=>false,'armed'=>true,'triggered'=>false];}
        if($doors!==[]){$di=$this->index($seed,'tripwire',count($doors));$d=$doors[$di]??null;if(is_array($d))$traps[]=['id'=>'forge-trap-'.substr(hash('sha256',$seed.'|tripwire|'.$di),0,12),'kind'=>'trap','trap_type'=>'tripwire','label'=>'Tripwire','effect'=>'A hidden line is pulled taut and the trap is sprung.','door_index'=>$di,'x'=>((float)($d['x1']??0)+(float)($d['x2']??0))/2,'y'=>((float)($d['y1']??0)+(float)($d['y2']??0))/2,'trigger_radius'=>max(.012,.62/max($cols,$rows)),'revealed'=>false,'armed'=>true,'triggered'=>false];}
        return$traps;
    }
    private function index(string $seed,string $salt,int $count):int{return$count<=1?0:(int)(hexdec(substr(hash('sha256',$seed.'|'.$salt),0,8))%$count);}
}
