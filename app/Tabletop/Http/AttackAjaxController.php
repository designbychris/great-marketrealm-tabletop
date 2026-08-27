<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Http;

use GreatMarketrealmTabletop\Tabletop\Battle\Exceptions\AttackDenied;
use GreatMarketrealmTabletop\Tabletop\Battle\Exceptions\BattleDeedDenied;
use GreatMarketrealmTabletop\Tabletop\Battle\Exceptions\TurnResourceSpent;
use GreatMarketrealmTabletop\Tabletop\Battle\Services\AttackManager;
use GreatMarketrealmTabletop\Tabletop\Encounters\Exceptions\StaleEncounterRevision;
use Throwable;

defined('ABSPATH') || exit;

final class AttackAjaxController
{
    public function __construct(
        private AttackManager $attacks
    ) {}

    public function attack(): void
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
            $result = $this->attacks->attack(
                sanitize_text_field(
                    (string) ($_POST['table_id'] ?? '')
                ),
                get_current_user_id(),
                sanitize_text_field(
                    (string) ($_POST['encounter_id'] ?? '')
                ),
                sanitize_text_field(
                    (string) ($_POST['target_token_id'] ?? '')
                ),
                max(1, (int) ($_POST['revision'] ?? 1))
            );

            wp_send_json_success([
                'encounter' => $result['encounter']->toArray(),
                'attack' => $result['outcome']->toArray(),
                'targeting' => $result['targeting']?->toArray(),
                'damage' => $result['damage']?->toArray(),
                'damage_adjustment' => $result['damage_adjustment']?->toArray(),
                'vitality' => $result['vitality']?->toArray(),
                'death_saves' => $result['death_saves']?->toArray(),
                'event' => $result['attack_event']->toArray(),
                'damage_event' => $result['damage_event']?->toArray(),
            ]);
        } catch (StaleEncounterRevision $exception) {
            wp_send_json_error(
                ['message' => $exception->getMessage()],
                409
            );
        } catch (
            AttackDenied
            | BattleDeedDenied
            | TurnResourceSpent $exception
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
