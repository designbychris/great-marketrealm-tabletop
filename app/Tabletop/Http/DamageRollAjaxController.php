<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Http;

use GreatMarketrealmTabletop\Tabletop\Battle\Exceptions\AttackDenied;
use GreatMarketrealmTabletop\Tabletop\Battle\Services\DamageRollManager;
use Throwable;

defined('ABSPATH') || exit;

final class DamageRollAjaxController
{
    public function __construct(private DamageRollManager $damage) {}

    public function roll(): void
    {
        if (! is_user_logged_in()) {
            wp_send_json_error(['message' => 'Authentication required.'], 401);
        }

        check_ajax_referer(TabletopAjaxController::NONCE_ACTION, 'nonce');

        try {
            $result = $this->damage->roll(
                sanitize_text_field((string) ($_POST['table_id'] ?? '')),
                get_current_user_id(),
                sanitize_text_field((string) ($_POST['encounter_id'] ?? '')),
                sanitize_text_field((string) ($_POST['attack_event_id'] ?? ''))
            );

            wp_send_json_success([
                'damage' => $result['damage']->toArray(),
                'damage_adjustment' => $result['damage_adjustment']->toArray(),
                'vitality' => $result['vitality']->toArray(),
                'death_saves' => $result['death_saves']->toArray(),
                'damage_event' => $result['damage_event']->toArray(),
            ]);
        } catch (AttackDenied $exception) {
            wp_send_json_error(['message' => $exception->getMessage()], 403);
        } catch (Throwable $exception) {
            wp_send_json_error(['message' => $exception->getMessage()], 400);
        }
    }
}
