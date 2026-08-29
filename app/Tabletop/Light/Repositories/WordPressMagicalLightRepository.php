<?php

declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tabletop\Light\Repositories;
use GreatMarketrealmTabletop\Tabletop\Light\Contracts\MagicalLightRepository;
use GreatMarketrealmTabletop\Tabletop\Light\Models\MagicalLight;

final class WordPressMagicalLightRepository implements MagicalLightRepository
{
    private const OPTION='gmrt_magical_lights';
    /** @return array<int,MagicalLight> */
    public function forScene(string $tableId,string $sceneId): array
    {
        $all=$this->all(); $out=[]; $changed=false;
        foreach($all as $key=>$row){
            $light=MagicalLight::fromArray(is_array($row)?$row:[]);
            if($light->expired()){ unset($all[$key]); $changed=true; continue; }
            if($light->tableId()===$tableId && $light->sceneId()===$sceneId)$out[]=$light;
        }
        if($changed) update_option(self::OPTION,$all,false);
        return $out;
    }
    public function forToken(string $tableId,string $sceneId,string $tokenId): ?MagicalLight
    {
        foreach($this->forScene($tableId,$sceneId) as $light) if($light->tokenId()===$tokenId) return $light;
        return null;
    }
    public function save(MagicalLight $light): void
    {
        $all=$this->all();
        foreach($all as $key=>$row){$old=MagicalLight::fromArray(is_array($row)?$row:[]);if($old->tableId()===$light->tableId()&&$old->sceneId()===$light->sceneId()&&$old->tokenId()===$light->tokenId())unset($all[$key]);}
        $all[$light->id()]=$light->toArray(); update_option(self::OPTION,$all,false);
    }
    public function deleteForToken(string $tableId,string $sceneId,string $tokenId): void
    {
        $all=$this->all();
        foreach($all as $key=>$row){$old=MagicalLight::fromArray(is_array($row)?$row:[]);if($old->tableId()===$tableId&&$old->sceneId()===$sceneId&&$old->tokenId()===$tokenId)unset($all[$key]);}
        update_option(self::OPTION,$all,false);
    }
    /** @return array<string,array<string,mixed>> */
    private function all(): array { $value=get_option(self::OPTION,[]); return is_array($value)?$value:[]; }
}
