<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tabletop\Vision\Services;
use GreatMarketrealmTabletop\Tabletop\Vision\Models\VisionBarrier;
defined('ABSPATH') || exit;
final class SightLineResolver
{
    /** @param array<int,VisionBarrier> $barriers */
    public function canSee(int $fromColumn,int $fromRow,int $toColumn,int $toRow,array $barriers):bool
    {
        if($fromColumn===$toColumn&&$fromRow===$toRow){return true;}
        $ax=$fromColumn+.5;$ay=$fromRow+.5;$bx=$toColumn+.5;$by=$toRow+.5;
        foreach($barriers as $barrier){if($barrier instanceof VisionBarrier&&$barrier->blocksSight()&&$this->intersects($ax,$ay,$bx,$by,$barrier->x1(),$barrier->y1(),$barrier->x2(),$barrier->y2())){return false;}}
        return true;
    }
    private function intersects(float $ax,float $ay,float $bx,float $by,float $cx,float $cy,float $dx,float $dy):bool
    {
        $den=($bx-$ax)*($dy-$cy)-($by-$ay)*($dx-$cx);if(abs($den)<0.000001){return false;}
        $t=(($cx-$ax)*($dy-$cy)-($cy-$ay)*($dx-$cx))/$den;$u=(($cx-$ax)*($by-$ay)-($cy-$ay)*($bx-$ax))/$den;
        return $t>0.000001&&$t<0.999999&&$u>=-0.000001&&$u<=1.000001;
    }
}
