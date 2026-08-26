<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Presentation;

use GreatMarketrealmTabletop\Tabletop\Models\TabletopChamberState;

defined('ABSPATH') || exit;

final class TabletopChamberRenderer
{
    public function render(
        ?TabletopChamberState $state,
        ?string $message = null,
        bool $canPrepareTestTable = false
    ): string {
        $view = GMRT_PATH
            . 'app/Tabletop/Views/chamber.php';

        ob_start();
        require $view;

        return (string) ob_get_clean();
    }
}
