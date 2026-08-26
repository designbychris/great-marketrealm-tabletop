<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Http;

use GreatMarketrealmTabletop\Tabletop\Battle\Exceptions\BattleDeedDenied;
use GreatMarketrealmTabletop\Tabletop\Battle\Exceptions\TurnResourceSpent;
use GreatMarketrealmTabletop\Tabletop\Battle\Services\BattleDeedManager;
use GreatMarketrealmTabletop\Tabletop\Encounters\Exceptions\StaleEncounterRevision;
use Throwable;

defined('ABSPATH') || exit;

final class BattleDeedAjaxController
{
    public function __construct(
        private BattleDeedManager $deeds
    ) {}

    public function perform(): void
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
            $result = $this->deeds->perform(
                sanitize_text_field(
                    (string) ($_POST['table_id'] ?? '')
                ),
                get_current_user_id(),
                sanitize_text_field(
                    (string) ($_POST['encounter_id'] ?? '')
                ),
                sanitize_text_field(
                    (string) ($_POST['deed'] ?? '')
                ),
                max(
                    1,
                    (int) ($_POST['revision'] ?? 1)
                )
            );

            wp_send_json_success([
                'encounter' => $result['encounter']->toArray(),
                'event' => $result['event']->toArray(),
            ]);
        } catch (StaleEncounterRevision $exception) {
            wp_send_json_error(
                ['message' => $exception->getMessage()],
                409
            );
        } catch (
            BattleDeedDenied|TurnResourceSpent $exception
        ) {
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
