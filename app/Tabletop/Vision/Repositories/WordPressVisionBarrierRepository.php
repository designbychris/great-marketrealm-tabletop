<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tabletop\Vision\Repositories;
use GreatMarketrealmTabletop\Tabletop\Vision\Contracts\VisionBarrierRepository;
use GreatMarketrealmTabletop\Tabletop\Vision\Models\VisionBarrier;
defined('ABSPATH') || exit;
final class WordPressVisionBarrierRepository implements VisionBarrierRepository
{
    private const OPTION='gmrt_vision_barriers';
    public function forScene(string $tableId,string $sceneId):array{$records=$this->records()[$tableId][$sceneId]??[];if(!is_array($records)){return [];}return array_values(array_map(static fn(array $r):VisionBarrier=>VisionBarrier::reconstitute($r),array_filter($records,'is_array')));}
    public function save(string $tableId,VisionBarrier $barrier):void{$all=$this->records();$all[$tableId][$barrier->sceneId()][$barrier->id()]=$barrier->toArray();update_option(self::OPTION,$all,false);}
    public function delete(string $tableId,string $sceneId,string $barrierId):void{$all=$this->records();unset($all[$tableId][$sceneId][$barrierId]);update_option(self::OPTION,$all,false);}
    /** @return array<string,mixed> */ private function records():array{$r=get_option(self::OPTION,[]);return is_array($r)?$r:[];}
}
