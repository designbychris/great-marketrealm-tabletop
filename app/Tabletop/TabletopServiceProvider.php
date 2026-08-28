<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop;

use GreatMarketrealmTabletop\Tabletop\Encounters\Services\EncounterManagerFactory;
use GreatMarketrealmTabletop\Tabletop\Http\EncounterAjaxController;
use GreatMarketrealmTabletop\Tabletop\Http\BattleDeedAjaxController;
use GreatMarketrealmTabletop\Tabletop\Http\AttackAjaxController;
use GreatMarketrealmTabletop\Tabletop\Http\DeathSaveAjaxController;
use GreatMarketrealmTabletop\Tabletop\Http\ConditionAjaxController;
use GreatMarketrealmTabletop\Tabletop\Battle\Services\BattleDeedManagerFactory;
use GreatMarketrealmTabletop\Tabletop\Battle\Services\AttackManagerFactory;
use GreatMarketrealmTabletop\Tabletop\Battle\Services\DeathSaveManagerFactory;
use GreatMarketrealmTabletop\Tabletop\Conditions\Services\ConditionManagerFactory;
use GreatMarketrealmTabletop\Tabletop\Http\TabletopAjaxController;
use GreatMarketrealmTabletop\Tabletop\Http\TestTableAjaxController;
use GreatMarketrealmTabletop\Tabletop\Http\TargetingAjaxController;
use GreatMarketrealmTabletop\Tabletop\Http\CartographyAjaxController;
use GreatMarketrealmTabletop\Tabletop\Http\FogOfWarAjaxController;
use GreatMarketrealmTabletop\Tabletop\Http\VisionBarrierAjaxController;
use GreatMarketrealmTabletop\Tabletop\Http\GatheringAjaxController;
use GreatMarketrealmTabletop\Tabletop\Http\CompanionCharacterAjaxController;
use GreatMarketrealmTabletop\Tabletop\Http\QuickHandsAjaxController;
use GreatMarketrealmTabletop\Tabletop\Satchel\Services\QuickHandsRoller;
use GreatMarketrealmTabletop\Tabletop\Battle\Services\SecureD20Roller;
use GreatMarketrealmTabletop\Integration\Companion\WordPressCompanionCharacterGateway;
use GreatMarketrealmTabletop\Tables\Scenes\Repositories\WordPressTableSceneRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Repositories\WordPressTableTokenRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Services\TableTokenManagerFactory;
use GreatMarketrealmTabletop\Tables\Memberships\Services\TableGatheringFactory;
use GreatMarketrealmTabletop\Tables\Memberships\Repositories\WordPressTableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Repositories\WordPressTableMemberIdentityDirectory;
use GreatMarketrealmTabletop\Tables\Memberships\Delivery\WordPressTableInvitationDelivery;
use GreatMarketrealmTabletop\Tables\Repositories\WordPressTableRepository;
use GreatMarketrealmTabletop\Tabletop\Fog\Services\FogOfWarFactory;
use GreatMarketrealmTabletop\Tabletop\Vision\Services\VisionBarrierFactory;
use GreatMarketrealmTabletop\Tabletop\Cartography\Services\CartographersTableFactory;
use GreatMarketrealmTabletop\Tabletop\Battlefield\Services\TargetingServiceFactory;
use GreatMarketrealmTabletop\Tabletop\Testing\TestTableProvisioner;
use GreatMarketrealmTabletop\Tabletop\Movement\Services\TabletopMovementFactory;
use GreatMarketrealmTabletop\Tabletop\Presentation\TabletopChamberRenderer;
use GreatMarketrealmTabletop\Tabletop\Presentation\TabletopShortcode;
use GreatMarketrealmTabletop\Tabletop\Services\TabletopChamberFactory;

defined('ABSPATH') || exit;

final class TabletopServiceProvider
{
    private TabletopShortcode $shortcode;

    private TabletopAjaxController $ajax;

    private EncounterAjaxController $encounterAjax;

    private BattleDeedAjaxController $battleDeedAjax;

    private AttackAjaxController $attackAjax;

    private DeathSaveAjaxController $deathSaveAjax;

    private ConditionAjaxController $conditionAjax;

    private TestTableAjaxController $testTableAjax;

    private TargetingAjaxController $targetingAjax;

    private CartographyAjaxController $cartographyAjax;

    private FogOfWarAjaxController $fogAjax;

    private VisionBarrierAjaxController $visionAjax;

    private GatheringAjaxController $gatheringAjax;

    private CompanionCharacterAjaxController $companionCharacterAjax;

    private QuickHandsAjaxController $quickHandsAjax;

    public function __construct()
    {
        $chamber = TabletopChamberFactory::make();

        $this->shortcode = new TabletopShortcode(
            $chamber,
            new TabletopChamberRenderer(),
            new WordPressTableMembershipRepository()
        );

        $this->ajax = new TabletopAjaxController(
            $chamber,
            TabletopMovementFactory::make(),
            new TabletopChamberRenderer()
        );

        $this->encounterAjax = new EncounterAjaxController(
            EncounterManagerFactory::make()
        );

        $this->battleDeedAjax = new BattleDeedAjaxController(
            BattleDeedManagerFactory::make()
        );

        $this->attackAjax = new AttackAjaxController(
            AttackManagerFactory::make()
        );

        $this->deathSaveAjax = new DeathSaveAjaxController(
            DeathSaveManagerFactory::make()
        );

        $this->conditionAjax = new ConditionAjaxController(
            ConditionManagerFactory::make()
        );

        $this->testTableAjax = new TestTableAjaxController(
            new TestTableProvisioner()
        );

        $this->targetingAjax = new TargetingAjaxController(
            TargetingServiceFactory::make()
        );

        $this->cartographyAjax = new CartographyAjaxController(
            CartographersTableFactory::make()
        );

        $this->fogAjax = new FogOfWarAjaxController(
            FogOfWarFactory::make()
        );

        $this->visionAjax = new VisionBarrierAjaxController(
            VisionBarrierFactory::make()
        );

        $identityDirectory = new WordPressTableMemberIdentityDirectory();
        $this->gatheringAjax = new GatheringAjaxController(
            TableGatheringFactory::make(),
            new WordPressTableMembershipRepository(),
            $identityDirectory,
            new WordPressTableInvitationDelivery(
                new WordPressTableRepository(),
                $identityDirectory
            )
        );

        $this->companionCharacterAjax = new CompanionCharacterAjaxController(
            new WordPressCompanionCharacterGateway(),
            TableGatheringFactory::make(),
            new WordPressTableMembershipRepository(),
            new WordPressTableSceneRepository(),
            new WordPressTableTokenRepository(),
            TableTokenManagerFactory::make()
        );

        $this->quickHandsAjax = new QuickHandsAjaxController(
            new WordPressCompanionCharacterGateway(),
            new WordPressTableMembershipRepository(),
            new QuickHandsRoller(new SecureD20Roller())
        );
    }

    public function register(): void
    {
        add_shortcode(
            TabletopShortcode::TAG,
            [$this->shortcode, 'render']
        );

        add_action(
            'wp_ajax_gmrt_tabletop_state',
            [$this->ajax, 'state']
        );

        add_action(
            'wp_ajax_gmrt_tabletop_fragment',
            [$this->ajax, 'fragment']
        );

        add_action(
            'wp_ajax_gmrt_move_token',
            [$this->ajax, 'moveToken']
        );

        add_action(
            'wp_ajax_gmrt_begin_encounter',
            [$this->encounterAjax, 'begin']
        );

        add_action(
            'wp_ajax_gmrt_prepare_encounter',
            [$this->encounterAjax, 'prepare']
        );

        add_action(
            'wp_ajax_gmrt_add_combatant',
            [$this->encounterAjax, 'addCombatant']
        );

        add_action(
            'wp_ajax_gmrt_start_encounter',
            [$this->encounterAjax, 'start']
        );

        add_action(
            'wp_ajax_gmrt_pause_encounter',
            [$this->encounterAjax, 'pause']
        );

        add_action(
            'wp_ajax_gmrt_resume_encounter',
            [$this->encounterAjax, 'resume']
        );

        add_action(
            'wp_ajax_gmrt_advance_encounter',
            [$this->encounterAjax, 'advance']
        );

        add_action(
            'wp_ajax_gmrt_end_encounter',
            [$this->encounterAjax, 'end']
        );

        add_action(
            'wp_ajax_gmrt_perform_battle_deed',
            [$this->battleDeedAjax, 'perform']
        );

        add_action(
            'wp_ajax_gmrt_resolve_attack',
            [$this->attackAjax, 'attack']
        );

        add_action(
            'wp_ajax_gmrt_roll_death_save',
            [$this->deathSaveAjax, 'roll']
        );

        add_action(
            'wp_ajax_gmrt_apply_condition',
            [$this->conditionAjax, 'apply']
        );

        add_action(
            'wp_ajax_gmrt_remove_condition',
            [$this->conditionAjax, 'remove']
        );

        add_action(
            'wp_ajax_gmrt_prepare_test_table',
            [$this->testTableAjax, 'prepare']
        );

        add_action(
            'wp_ajax_gmrt_measure_target',
            [$this->targetingAjax, 'measure']
        );

        add_action(
            'wp_ajax_gmrt_replace_battlemap',
            [$this->cartographyAjax, 'replaceBattlemap']
        );

        add_action(
            'wp_ajax_gmrt_calibrate_grid',
            [$this->cartographyAjax, 'calibrateGrid']
        );

        add_action(
            'wp_ajax_gmrt_configure_fog',
            [$this->fogAjax, 'configure']
        );

        add_action(
            'wp_ajax_gmrt_add_vision_barrier',
            [$this->visionAjax, 'add']
        );

        add_action(
            'wp_ajax_gmrt_toggle_vision_door',
            [$this->visionAjax, 'toggle']
        );

        add_action(
            'wp_ajax_gmrt_remove_vision_barrier',
            [$this->visionAjax, 'remove']
        );

        add_action(
            'wp_ajax_gmrt_invite_table_player',
            [$this->gatheringAjax, 'invite']
        );

        add_action(
            'wp_ajax_gmrt_accept_table_invitation',
            [$this->gatheringAjax, 'accept']
        );

        add_action(
            'wp_ajax_gmrt_remove_table_player',
            [$this->gatheringAjax, 'remove']
        );

        add_action(
            'wp_ajax_gmrt_select_companion_character',
            [$this->companionCharacterAjax, 'select']
        );

        add_action(
            'wp_ajax_gmrt_quick_hands_roll',
            [$this->quickHandsAjax, 'roll']
        );
    }
}
