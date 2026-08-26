<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop;

use GreatMarketrealmTabletop\Tabletop\Http\TabletopController;
use GreatMarketrealmTabletop\Tabletop\Presentation\TabletopChamberRenderer;
use GreatMarketrealmTabletop\Tabletop\Routing\TabletopRoute;
use GreatMarketrealmTabletop\Tabletop\Services\TabletopChamberFactory;

defined('ABSPATH') || exit;

final class TabletopServiceProvider
{
    private TabletopRoute $route;

    private TabletopController $controller;

    public function __construct()
    {
        $this->route = new TabletopRoute();
        $this->controller = new TabletopController(
            $this->route,
            TabletopChamberFactory::make(),
            new TabletopChamberRenderer()
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
    }
}
