<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Http;

use GreatMarketrealmTabletop\Tabletop\Exceptions\TabletopAccessDenied;
use GreatMarketrealmTabletop\Tabletop\Presentation\TabletopChamberRenderer;
use GreatMarketrealmTabletop\Tabletop\Routing\TabletopRoute;
use GreatMarketrealmTabletop\Tabletop\Services\TabletopChamber;
use Throwable;

defined('ABSPATH') || exit;

final class TabletopController
{
    public function __construct(
        private TabletopRoute $route,
        private TabletopChamber $chamber,
        private TabletopChamberRenderer $renderer
    ) {}

    public function dispatch(): void
    {
        if (! $this->route->matches()) {
            return;
        }

        if (! is_user_logged_in()) {
            auth_redirect();
            exit;
        }

        $tableId = $this->route->tableId();

        if ($tableId === '') {
            status_header(200);
            $this->enqueueAssets();
            $this->renderer->render(
                null,
                'Choose an active Table to enter the Tabletop Chamber.'
            );
            exit;
        }

        try {
            $state = $this->chamber->state(
                $tableId,
                get_current_user_id()
            );

            status_header(200);
            $message = null;
        } catch (TabletopAccessDenied $exception) {
            status_header(403);
            $state = null;
            $message = $exception->getMessage();
        } catch (Throwable $exception) {
            status_header(404);
            $state = null;
            $message = $exception->getMessage();
        }

        $this->enqueueAssets();
        $this->renderer->render(
            $state,
            $message
        );
        exit;
    }

    private function enqueueAssets(): void
    {
        wp_enqueue_style(
            'gmrt-tabletop',
            GMRT_URL . 'assets/css/tabletop.css',
            [],
            GMRT_VERSION
        );
    }
}
