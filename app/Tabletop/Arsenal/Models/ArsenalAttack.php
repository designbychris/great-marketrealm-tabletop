<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tabletop\Arsenal\Models;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\CombatProfile;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\DamageProfile;
use InvalidArgumentException;
defined('ABSPATH') || exit;
final class ArsenalAttack
{
    public function __construct(
        private string $id,
        private string $tokenId,
        private string $name,
        private string $kind,
        private CombatProfile $combat,
        private DamageProfile $damage,
        private array $properties=[],
        private string $sourceType='tabletop',
        private ?string $sourceReference=null
    ){
        if(trim($id)===''||trim($tokenId)===''){throw new InvalidArgumentException('An arsenal attack requires IDs.');}
        if(trim($name)===''){throw new InvalidArgumentException('An arsenal attack requires a name.');}
        AttackKind::assert($kind);
        if($combat->tokenId()!==$tokenId||$damage->tokenId()!==$tokenId){throw new InvalidArgumentException('Arsenal profiles must belong to the attack token.');}
    }
    public function id():string{return $this->id;}
    public function tokenId():string{return $this->tokenId;}
    public function name():string{return $this->name;}
    public function kind():string{return $this->kind;}
    public function combat():CombatProfile{return $this->combat;}
    public function damage():DamageProfile{return $this->damage;}
    public function sourceType():string{return $this->sourceType;}
    public function sourceReference():?string{return $this->sourceReference;}
    public function toArray():array{return [
        'id'=>$this->id,'token_id'=>$this->tokenId,'name'=>$this->name,'kind'=>$this->kind,
        'combat'=>$this->combat->toArray(),'damage'=>$this->damage->toArray(),
        'properties'=>array_values($this->properties),'source_type'=>$this->sourceType,'source_reference'=>$this->sourceReference,
    ];}
    public static function reconstitute(array $record):self
    {
        $token=(string)($record['token_id']??'');
        $combat=is_array($record['combat']??null)?$record['combat']:[];
        $damage=is_array($record['damage']??null)?$record['damage']:[];
        $combat['token_id']=$token;$damage['token_id']=$token;
        return new self(
            (string)($record['id']??''),$token,(string)($record['name']??''),
            (string)($record['kind']??AttackKind::IMPROVISED),
            CombatProfile::reconstitute($combat),DamageProfile::reconstitute($damage),
            is_array($record['properties']??null)?array_map('strval',$record['properties']):[],
            (string)($record['source_type']??'tabletop'),
            isset($record['source_reference'])?(string)$record['source_reference']:null
        );
    }
}
