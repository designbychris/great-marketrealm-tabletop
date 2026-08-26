<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Http;

use GreatMarketrealmTabletop\Tabletop\Battle\Exceptions\DeathSaveDenied;
use GreatMarketrealmTabletop\Tabletop\Battle\Services\DeathSaveManager;
use Throwable;

defined('ABSPATH') || exit;

final class DeathSaveAjaxController
{
    public function __construct(
        private DeathSaveManager $deathSaves
    ) {}

    public function roll(): void
    {
        if (! is_user_logged_in()) {
            wp_send_json_error(
                ['message' => 'Authentication required.'],
                401
            );
        }

        check_ajax_referer(
            TabletopAjaxController::NONCE_ACTION,
            'nonce'
        );

        try {
            $result = $this->deathSaves->roll(
                sanitize_text_field(
                    (string) ($_POST['table_id'] ?? '')
                ),
                get_current_user_id(),
                sanitize_text_field(
                    (string) ($_POST['encounter_id'] ?? '')
                ),
                max(1, (int) ($_POST['revision'] ?? 1))
            );

            wp_send_json_success([
                'encounter' => $result['encounter']->toArray(),
                'death_save' => $result['outcome']->toArray(),
                'death_saves' => $result['death_saves']->toArray(),
                'vitality' => $result['vitality']->toArray(),
                'event' => $result['event']->toArray(),
            ]);
        } catch (DeathSaveDenied $exception) {
            wp_send_json_error(
                ['message' => $exception->getMessage()],
                403
            );
        } catch (Throwable $exception) {
            wp_send_json_error(
                ['message' => $exception->getMessage()],
                400
            );
        }
    }
}
