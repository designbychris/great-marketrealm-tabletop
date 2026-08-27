<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tabletop\Arsenal\Contracts;
use GreatMarketrealmTabletop\Tabletop\Arsenal\Models\CombatArsenal;
defined('ABSPATH') || exit;
interface CombatArsenalRepository
{
    public function forToken(string $tableId,string $tokenId):CombatArsenal;
    public function save(string $tableId,CombatArsenal $arsenal):void;
}
