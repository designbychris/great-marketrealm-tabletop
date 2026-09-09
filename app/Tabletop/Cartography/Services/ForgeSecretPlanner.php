<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Cartography\Services;

defined('ABSPATH') || exit;

/** Deterministic Keeper-secret features for forged dungeons. */
final class ForgeSecretPlanner
{
    /** @param array<string,mixed> $plan @return array<int,array<string,mixed>> */
    public function plan(array $plan): array
    {
        if (empty($plan['include_secrets']) || ($plan['scene_type'] ?? 'dungeon') !== 'dungeon') return [];
        $seed=(string)($plan['seed'] ?? 'Peppercorn-01');
        $doors=is_array($plan['doors'] ?? null)?array_values($plan['doors']):[];
        $rooms=is_array($plan['rooms'] ?? null)?array_values($plan['rooms']):[];
        $secrets=[];
        if (count($doors) >= 2) {
            $index=$this->index($seed,'secret-door',count($doors));
            $door=$doors[$index];
            if (is_array($door)) $secrets[]=[
                'id'=>'secret-door-'.substr(hash('sha256',$seed.'|'.$index),0,12),
                'kind'=>'secret-door','label'=>'Concealed Door','door_index'=>$index,
                'x'=>(((float)($door['x1']??0))+((float)($door['x2']??0)))/2,
                'y'=>(((float)($door['y1']??0))+((float)($door['y2']??0)))/2,
                'revealed'=>false,
            ];
        }
        $eligible=[];
        foreach($rooms as $index=>$room){
            if(!is_array($room)||($room['role']??'')==='lair'||!empty($room['boss_lair'])) continue;
            if((float)($room['w']??0)<4||(float)($room['h']??0)<4) continue;
            $eligible[$index]=$room;
        }
        if($eligible!==[]){
            $keys=array_keys($eligible); $roomIndex=$keys[$this->index($seed,'hidden-cache',count($keys))]; $room=$eligible[$roomIndex];
            $cols=max(1,(int)($plan['cols']??1)); $rows=max(1,(int)($plan['rows']??1));
            $secrets[]=[
                'id'=>'secret-cache-'.substr(hash('sha256',$seed.'|'.$roomIndex),0,12),
                'kind'=>'hidden-cache','label'=>'Hidden Cache','room_index'=>$roomIndex,
                'x'=>(((float)($room['x']??0))+((float)($room['w']??0)*0.72))/$cols,
                'y'=>(((float)($room['y']??0))+((float)($room['h']??0)*0.28))/$rows,
                'revealed'=>false,
            ];
        }
        return $secrets;
    }
    private function index(string $seed,string $salt,int $count):int{return $count<=1?0:(int)(hexdec(substr(hash('sha256',$seed.'|'.$salt),0,8))%$count);}
}
