<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop;

use GreatMarketrealmTabletop\Tabletop\Http\TabletopController;
use GreatMarketrealmTabletop\Tabletop\Http\TabletopAjaxController;
use GreatMarketrealmTabletop\Tabletop\Movement\Services\TabletopMovementFactory;
use GreatMarketrealmTabletop\Tabletop\Presentation\TabletopChamberRenderer;
use GreatMarketrealmTabletop\Tabletop\Routing\TabletopRoute;
use GreatMarketrealmTabletop\Tabletop\Services\TabletopChamberFactory;

defined('ABSPATH') || exit;

final class TabletopServiceProvider
{
    private TabletopRoute $route;

    private TabletopController $controller;

    private TabletopAjaxController $ajax;

    public function __construct()
    {
        $this->route = new TabletopRoute();
        $chamber = TabletopChamberFactory::make();

        $this->controller = new TabletopController(
            $this->route,
            $chamber,
            new TabletopChamberRenderer()
        );

        $this->ajax = new TabletopAjaxController(
            $chamber,
            TabletopMovementFactory::make()
        );
    }

    public function register(): void
    {
        add_action(
            'init',
            [$this->route, 'register']
        );

        add_filter(
            'query_vars',
            [$this->route, 'queryVars']
        );

        add_action(
            'template_redirect',
            [$this->controller, 'dispatch']
        );

        add_action(
            'wp_ajax_gmrt_tabletop_state',
            [$this->ajax, 'state']
        );

        add_action(
            'wp_ajax_gmrt_move_token',
            [$this->ajax, 'moveToken']
        );
    }
}
