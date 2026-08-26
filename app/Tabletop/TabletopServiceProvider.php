<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop;

use GreatMarketrealmTabletop\Tabletop\Encounters\Services\EncounterManagerFactory;
use GreatMarketrealmTabletop\Tabletop\Http\EncounterAjaxController;
use GreatMarketrealmTabletop\Tabletop\Http\BattleDeedAjaxController;
use GreatMarketrealmTabletop\Tabletop\Battle\Services\BattleDeedManagerFactory;
use GreatMarketrealmTabletop\Tabletop\Http\TabletopAjaxController;
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
    }
}
