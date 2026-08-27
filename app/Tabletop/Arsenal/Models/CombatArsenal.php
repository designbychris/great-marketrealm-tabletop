<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tabletop\Arsenal\Models;
use InvalidArgumentException;
defined('ABSPATH') || exit;
final class CombatArsenal
{
    public function __construct(private string $tokenId,private array $attacks)
    {
        if(trim($tokenId)===''){throw new InvalidArgumentException('A combat arsenal requires a token ID.');}
        foreach($attacks as $attack){if(!$attack instanceof ArsenalAttack||$attack->tokenId()!==$tokenId){throw new InvalidArgumentException('Every arsenal attack must belong to its token.');}}
    }
    public function tokenId():string{return $this->tokenId;}
    public function attacks():array{return $this->attacks;}
    public function find(string $attackId):?ArsenalAttack{foreach($this->attacks as $attack){if($attack->id()===$attackId){return $attack;}}return null;}
    public function toArray():array{return ['token_id'=>$this->tokenId,'attacks'=>array_map(static fn(ArsenalAttack $a):array=>$a->toArray(),$this->attacks)];}
}
