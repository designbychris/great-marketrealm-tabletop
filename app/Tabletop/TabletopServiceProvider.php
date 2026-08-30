<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop;

use GreatMarketrealmTabletop\Tabletop\Encounters\Repositories\WordPressEncounterRepository;
use GreatMarketrealmTabletop\Tabletop\Encounters\Services\EncounterManagerFactory;
use GreatMarketrealmTabletop\Tabletop\Http\EncounterAjaxController;
use GreatMarketrealmTabletop\Tabletop\Http\BattleDeedAjaxController;
use GreatMarketrealmTabletop\Tabletop\Http\AttackAjaxController;
use GreatMarketrealmTabletop\Tabletop\Http\DamageRollAjaxController;
use GreatMarketrealmTabletop\Tabletop\Http\DeathSaveAjaxController;
use GreatMarketrealmTabletop\Tabletop\Http\ConditionAjaxController;
use GreatMarketrealmTabletop\Tabletop\Battle\Services\BattleDeedManagerFactory;
use GreatMarketrealmTabletop\Tabletop\Battle\Services\AttackManagerFactory;
use GreatMarketrealmTabletop\Tabletop\Battle\Services\DamageRollManagerFactory;
use GreatMarketrealmTabletop\Tabletop\Battle\Services\DeathSaveManagerFactory;
use GreatMarketrealmTabletop\Tabletop\Conditions\Services\ConditionManagerFactory;
use GreatMarketrealmTabletop\Tabletop\Http\TabletopAjaxController;
use GreatMarketrealmTabletop\Tabletop\Http\TableTokenRemovalAjaxController;
use GreatMarketrealmTabletop\Tabletop\Tokens\Services\TableTokenRemoval;
use GreatMarketrealmTabletop\Tabletop\Http\TestTableAjaxController;
use GreatMarketrealmTabletop\Tabletop\Http\TargetingAjaxController;
use GreatMarketrealmTabletop\Tabletop\Http\CartographyAjaxController;
use GreatMarketrealmTabletop\Tabletop\Http\FogOfWarAjaxController;
use GreatMarketrealmTabletop\Tabletop\Http\VisionBarrierAjaxController;
use GreatMarketrealmTabletop\Tabletop\Http\GatheringAjaxController;
use GreatMarketrealmTabletop\Tabletop\Http\CompanionCharacterAjaxController;
use GreatMarketrealmTabletop\Tabletop\Http\AdventuringMeasuresAjaxController;
use GreatMarketrealmTabletop\Tabletop\Http\QuickHandsAjaxController;
use GreatMarketrealmTabletop\Tabletop\Http\WeaponHandsAjaxController;
use GreatMarketrealmTabletop\Tabletop\Http\SpellPouchAjaxController;
use GreatMarketrealmTabletop\Tabletop\Http\TableColourAjaxController;
use GreatMarketrealmTabletop\Tabletop\Http\CarriedLightAjaxController;
use GreatMarketrealmTabletop\Tabletop\Http\DroppedLightAjaxController;
use GreatMarketrealmTabletop\Tabletop\Http\MagicalLightAjaxController;
use GreatMarketrealmTabletop\Tabletop\Http\KeepersAtlasAjaxController;
use GreatMarketrealmTabletop\Tabletop\Http\BestiaryAjaxController;
use GreatMarketrealmTabletop\Tabletop\Atlas\Services\KeepersAtlasFactory;
use GreatMarketrealmTabletop\Tabletop\Bestiary\Services\BestiaryDeploymentManagerFactory;
use GreatMarketrealmTabletop\Tabletop\Light\Repositories\WordPressCarriedLightRepository;
use GreatMarketrealmTabletop\Tabletop\Light\Repositories\WordPressDroppedLightRepository;
use GreatMarketrealmTabletop\Tabletop\Light\Repositories\WordPressMagicalLightRepository;
use GreatMarketrealmTabletop\Tabletop\Satchel\Services\QuickHandsRoller;
use GreatMarketrealmTabletop\Tabletop\Satchel\Services\WeaponHandsRoller;
use GreatMarketrealmTabletop\Tabletop\Satchel\Services\SpellPouchRoller;
use GreatMarketrealmTabletop\Tabletop\Battle\Services\SecureD20Roller;
use GreatMarketrealmTabletop\Tabletop\Battle\Services\SecureDamageDieRoller;
use GreatMarketrealmTabletop\Tabletop\Chronicle\Services\TableChronicleRecorder;
use GreatMarketrealmTabletop\Tabletop\Chronicle\Repositories\WordPressChamberChronicleRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Repositories\WordPressBattleEventRepository;
use GreatMarketrealmTabletop\Tables\Services\SystemTableClock;
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

    private DamageRollAjaxController $damageRollAjax;

    private DeathSaveAjaxController $deathSaveAjax;

    private ConditionAjaxController $conditionAjax;

    private TestTableAjaxController $testTableAjax;

    private TargetingAjaxController $targetingAjax;

    private CartographyAjaxController $cartographyAjax;

    private FogOfWarAjaxController $fogAjax;

    private VisionBarrierAjaxController $visionAjax;

    private GatheringAjaxController $gatheringAjax;
    private TableTokenRemovalAjaxController $tokenRemovalAjax;

    private CompanionCharacterAjaxController $companionCharacterAjax;

    private AdventuringMeasuresAjaxController $adventuringMeasuresAjax;

    private QuickHandsAjaxController $quickHandsAjax;

    private WeaponHandsAjaxController $weaponHandsAjax;

    private SpellPouchAjaxController $spellPouchAjax;

    private TableColourAjaxController $tableColourAjax;

    private CarriedLightAjaxController $carriedLightAjax;

    private DroppedLightAjaxController $droppedLightAjax;

    private MagicalLightAjaxController $magicalLightAjax;

    private KeepersAtlasAjaxController $keepersAtlasAjax;

    private BestiaryAjaxController $bestiaryAjax;

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

        $this->damageRollAjax = new DamageRollAjaxController(
            DamageRollManagerFactory::make()
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

        $this->tokenRemovalAjax = new TableTokenRemovalAjaxController(
            new TableTokenRemoval(
                new WordPressTableMembershipRepository(),
                new WordPressTableTokenRepository(),
                new WordPressEncounterRepository(),
                new WordPressMagicalLightRepository()
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

        $this->tableColourAjax = new TableColourAjaxController(new WordPressTableMembershipRepository());

        $this->carriedLightAjax = new CarriedLightAjaxController(
            new WordPressTableMembershipRepository(),
            new WordPressTableSceneRepository(),
            new WordPressTableTokenRepository(),
            new WordPressCarriedLightRepository(),
            new WordPressDroppedLightRepository()
        );

        $this->droppedLightAjax = new DroppedLightAjaxController(
            new WordPressTableMembershipRepository(),
            new WordPressTableSceneRepository(),
            new WordPressTableTokenRepository(),
            new WordPressCarriedLightRepository(),
            new WordPressDroppedLightRepository()
        );


        $this->keepersAtlasAjax = new KeepersAtlasAjaxController(
            KeepersAtlasFactory::make()
        );

        $this->bestiaryAjax = new BestiaryAjaxController(
            BestiaryDeploymentManagerFactory::make()
        );

        $this->magicalLightAjax = new MagicalLightAjaxController(
            new WordPressCompanionCharacterGateway(),
            new WordPressTableMembershipRepository(),
            new WordPressTableSceneRepository(),
            new WordPressTableTokenRepository(),
            new WordPressMagicalLightRepository()
        );

        $this->adventuringMeasuresAjax = new AdventuringMeasuresAjaxController(
            new WordPressCompanionCharacterGateway(),
            new WordPressTableMembershipRepository()
        );

        $chronicle = new TableChronicleRecorder(
            new WordPressTableSceneRepository(),
            new WordPressEncounterRepository(),
            new WordPressTableTokenRepository(),
            new WordPressBattleEventRepository(),
            new WordPressChamberChronicleRepository(),
            new SystemTableClock()
        );

        $this->quickHandsAjax = new QuickHandsAjaxController(
            new WordPressCompanionCharacterGateway(),
            new WordPressTableMembershipRepository(),
            new QuickHandsRoller(new SecureD20Roller()),
            $chronicle
        );

        $this->weaponHandsAjax = new WeaponHandsAjaxController(
            new WordPressCompanionCharacterGateway(),
            new WordPressTableMembershipRepository(),
            new WeaponHandsRoller(
                new SecureD20Roller(),
                new SecureDamageDieRoller()
            ),
            $chronicle
        );

        $this->spellPouchAjax = new SpellPouchAjaxController(
            new WordPressCompanionCharacterGateway(),
            new WordPressTableMembershipRepository(),
            new SpellPouchRoller(
                new SecureD20Roller(),
                new SecureDamageDieRoller()
            ),
            $chronicle
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
            'wp_ajax_gmrt_remove_chamber_token',
            [$this->tokenRemovalAjax, 'remove']
        );


        add_action(
            'wp_ajax_gmrt_atlas_add_map',
            [$this->keepersAtlasAjax, 'addMap']
        );

        add_action(
            'wp_ajax_gmrt_atlas_open_map',
            [$this->keepersAtlasAjax, 'openMap']
        );

        add_action(
            'wp_ajax_gmrt_atlas_delete_map',
            [$this->keepersAtlasAjax, 'deleteMap']
        );

        add_action(
            'wp_ajax_gmrt_atlas_arrive_at_threshold',
            [$this->keepersAtlasAjax, 'arriveAtThreshold']
        );

        add_action(
            'wp_ajax_gmrt_atlas_place_threshold',
            [$this->keepersAtlasAjax, 'placeThreshold']
        );

        add_action(
            'wp_ajax_gmrt_atlas_move_threshold',
            [$this->keepersAtlasAjax, 'moveThreshold']
        );

        add_action(
            'wp_ajax_gmrt_atlas_remove_threshold',
            [$this->keepersAtlasAjax, 'removeThreshold']
        );

        add_action(
            'wp_ajax_gmrt_bestiary_deploy_at_point',
            [$this->bestiaryAjax, 'deployAtPoint']
        );

        add_action(
            'wp_ajax_gmrt_bestiary_deploy_at_threshold',
            [$this->bestiaryAjax, 'deployAtThreshold']
        );

        add_action(
            'wp_ajax_gmrt_toggle_magical_light',
            [$this->magicalLightAjax, 'toggle']
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
            'wp_ajax_gmrt_roll_attack_damage',
            [$this->damageRollAjax, 'roll']
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
            'wp_ajax_gmrt_choose_table_colour',
            [$this->tableColourAjax, 'choose']
        );

        add_action(
            'wp_ajax_gmrt_toggle_carried_light',
            [$this->carriedLightAjax, 'toggle']
        );

        add_action(
            'wp_ajax_gmrt_tend_dropped_light',
            [$this->droppedLightAjax, 'tend']
        );

        add_action(
            'wp_ajax_gmrt_update_adventuring_measures',
            [$this->adventuringMeasuresAjax, 'update']
        );

        add_action(
            'wp_ajax_gmrt_quick_hands_roll',
            [$this->quickHandsAjax, 'roll']
        );

        add_action(
            'wp_ajax_gmrt_weapon_hands_roll',
            [$this->weaponHandsAjax, 'roll']
        );

        add_action(
            'wp_ajax_gmrt_spell_pouch_roll',
            [$this->spellPouchAjax, 'roll']
        );
    }
}
