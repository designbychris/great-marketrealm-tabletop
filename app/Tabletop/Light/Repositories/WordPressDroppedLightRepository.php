<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tabletop\Light\Repositories;
use GreatMarketrealmTabletop\Tabletop\Light\Contracts\DroppedLightRepository;
use GreatMarketrealmTabletop\Tabletop\Light\Models\DroppedLight;
defined('ABSPATH') || exit;
final class WordPressDroppedLightRepository implements DroppedLightRepository
{
    private const OPTION='gmrt_dropped_lights';
    public function forScene(string $tableId,string $sceneId): array {
        $all=get_option(self::OPTION,[]); $rows=is_array($all)?($all[$tableId][$sceneId]??[]):[]; $out=[];
        foreach($rows as $row) if(is_array($row)) $out[]=DroppedLight::fromArray($row); return $out;
    }
    public function save(DroppedLight $light): void {
        $all=get_option(self::OPTION,[]); if(!is_array($all))$all=[];
        $all[$light->tableId()][$light->sceneId()][$light->id()]=$light->toArray(); update_option(self::OPTION,$all,false);
    }
    public function delete(string $tableId,string $sceneId,string $lightId): void {
        $all=get_option(self::OPTION,[]); if(!is_array($all))return; unset($all[$tableId][$sceneId][$lightId]);
        if(empty($all[$tableId][$sceneId]))unset($all[$tableId][$sceneId]); if(empty($all[$tableId]))unset($all[$tableId]); update_option(self::OPTION,$all,false);
    }
}
