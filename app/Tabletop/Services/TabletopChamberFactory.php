<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Services;

use GreatMarketrealmTabletop\Tables\Memberships\Repositories\WordPressTableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Repositories\WordPressTableRepository;
use GreatMarketrealmTabletop\Tables\Scenes\Repositories\WordPressTableSceneRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Repositories\WordPressTableTokenRepository;
use GreatMarketrealmTabletop\Tabletop\Encounters\Repositories\WordPressEncounterRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Repositories\WordPressVitalityRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Repositories\WordPressDeathSaveRepository;
use GreatMarketrealmTabletop\Tabletop\Conditions\Repositories\WordPressConditionRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Repositories\WordPressBattleEventRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Presentation\BattleLogProjector;
use GreatMarketrealmTabletop\Tabletop\Presentation\CombatantStateProjector;
use GreatMarketrealmTabletop\Tabletop\Arsenal\Repositories\WordPressCombatArsenalRepository;
use GreatMarketrealmTabletop\Tabletop\Fog\Repositories\WordPressFogOfWarRepository;
use GreatMarketrealmTabletop\Tabletop\Fog\Services\FogOfWarProjector;
use GreatMarketrealmTabletop\Tabletop\Vision\Repositories\WordPressVisionBarrierRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Repositories\WordPressTableMemberIdentityDirectory;
use GreatMarketrealmTabletop\Integration\Companion\WordPressCompanionCharacterGateway;
use GreatMarketrealmTabletop\Tabletop\Chronicle\Repositories\WordPressChamberChronicleRepository;
use GreatMarketrealmTabletop\Tabletop\Chronicle\Presentation\ChamberChronicleProjector;
use GreatMarketrealmTabletop\Tabletop\Footsteps\Repositories\WordPressFootstepTrailRepository;
use GreatMarketrealmTabletop\Tabletop\Footsteps\Presentation\FootstepTrailProjector;
use GreatMarketrealmTabletop\Tabletop\Light\Repositories\WordPressCarriedLightRepository;
use GreatMarketrealmTabletop\Tabletop\Light\Repositories\WordPressDroppedLightRepository;
use GreatMarketrealmTabletop\Tabletop\Light\Repositories\WordPressMagicalLightRepository;
use GreatMarketrealmTabletop\Tabletop\Atlas\Thresholds\Repositories\WordPressThresholdRepository;
use GreatMarketrealmTabletop\Tabletop\Bestiary\Repositories\TrainingBestiaryRepository;

defined('ABSPATH') || exit;

final class TabletopChamberFactory
{
    public static function make(): TabletopChamber
    {
        return new TabletopChamber(
            new WordPressTableRepository(),
            new WordPressTableMembershipRepository(),
            new WordPressTableSceneRepository(),
            new WordPressTableTokenRepository(),
            new WordPressEncounterRepository(),
            new WordPressVitalityRepository(),
            new WordPressDeathSaveRepository(),
            new WordPressConditionRepository(),
            new WordPressBattleEventRepository(),
            new BattleLogProjector(),
            new CombatantStateProjector(),
            new WordPressCombatArsenalRepository(),
            new WordPressFogOfWarRepository(),
            new FogOfWarProjector(),
            new WordPressVisionBarrierRepository(),
            new WordPressTableMemberIdentityDirectory(),
            new WordPressCompanionCharacterGateway(),
            new WordPressChamberChronicleRepository(),
            new ChamberChronicleProjector(),
            new WordPressFootstepTrailRepository(),
            new FootstepTrailProjector(),
            new WordPressCarriedLightRepository(),
            new WordPressDroppedLightRepository(),
            new WordPressMagicalLightRepository(),
            new WordPressThresholdRepository(),
            new TrainingBestiaryRepository()
        );
    }
}
