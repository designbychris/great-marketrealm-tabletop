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

    public function __construct()
    {
        $chamber = TabletopChamberFactory::make();

        $this->shortcode = new TabletopShortcode(
            $chamber,
            new TabletopChamberRenderer()
        );

        $this->ajax = new TabletopAjaxController(
            $chamber,
            TabletopMovementFactory::make()
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
            'wp_ajax_gmrt_move_token',
            [$this->ajax, 'moveToken']
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
    }
}
