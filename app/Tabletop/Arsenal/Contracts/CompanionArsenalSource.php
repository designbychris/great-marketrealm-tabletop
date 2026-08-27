<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tabletop\Arsenal\Contracts;
use GreatMarketrealmTabletop\Tabletop\Arsenal\Models\CombatArsenal;
defined('ABSPATH') || exit;
/**
 * Future GMRC boundary. GMRT consumes a certified combat projection rather
 * than importing the complete Companion character-sheet domain.
 */
interface CompanionArsenalSource
{
    public function forCompanionCharacter(string $opaqueCharacterReference,string $tableTokenId):?CombatArsenal;
}
