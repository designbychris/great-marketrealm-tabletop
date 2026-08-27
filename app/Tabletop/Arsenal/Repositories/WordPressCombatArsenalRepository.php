<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tabletop\Arsenal\Repositories;
use GreatMarketrealmTabletop\Tabletop\Arsenal\Contracts\CombatArsenalRepository;
use GreatMarketrealmTabletop\Tabletop\Arsenal\Models\ArsenalAttack;
use GreatMarketrealmTabletop\Tabletop\Arsenal\Models\CombatArsenal;
defined('ABSPATH') || exit;
final class WordPressCombatArsenalRepository implements CombatArsenalRepository
{
    private const OPTION='gmrt_combat_arsenals';
    public function forToken(string $tableId,string $tokenId):CombatArsenal
    {
        $records=$this->records();$record=$records[$tableId][$tokenId]??null;
        if(!is_array($record)){return new CombatArsenal($tokenId,[]);}
        $attacks=[];foreach($record['attacks']??[] as $attack){if(is_array($attack)){$attacks[]=ArsenalAttack::reconstitute($attack);}}
        return new CombatArsenal($tokenId,$attacks);
    }
    public function save(string $tableId,CombatArsenal $arsenal):void
    {
        $records=$this->records();$records[$tableId][$arsenal->tokenId()]=$arsenal->toArray();
        update_option(self::OPTION,$records,false);
    }
    private function records():array{$records=get_option(self::OPTION,[]);return is_array($records)?$records:[];}
}
