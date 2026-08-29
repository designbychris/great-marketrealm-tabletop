<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Http;

use GreatMarketrealmTabletop\Tabletop\Exceptions\TabletopAccessDenied;
use GreatMarketrealmTabletop\Tabletop\Movement\Exceptions\StaleTokenRevision;
use GreatMarketrealmTabletop\Tabletop\Movement\Exceptions\TabletopMovementDenied;
use GreatMarketrealmTabletop\Tabletop\Movement\Services\TabletopMovement;
use GreatMarketrealmTabletop\Tabletop\Presentation\TabletopChamberRenderer;
use GreatMarketrealmTabletop\Tabletop\Services\TabletopChamber;
use Throwable;

defined('ABSPATH') || exit;

final class TabletopAjaxController
{
    public const NONCE_ACTION = 'gmrt_tabletop_state';

    public function __construct(
        private TabletopChamber $chamber,
        private TabletopMovement $movement,
        private TabletopChamberRenderer $renderer
    ) {}

    public function state(): void
    {
        $this->guard();

        try {
            $state = $this->chamber->state(
                $this->tableId(),
                get_current_user_id()
            );

            wp_send_json_success([
                'table' => $state->table(),
                'viewer' => $state->viewer(),
                'members' => $state->members(),
                'scene' => $state->scene(),
                'tokens' => $state->tokens(),
                'encounter' => $state->encounter(),
                'vitality' => $state->vitality(),
                'death_saves' => $state->deathSaves(),
                'conditions' => $state->conditions(),
                'battle_log' => $state->battleLog(),
                'chamber_log' => $state->chamberLog(),
                'combatant_states' => $state->combatantStates(),
                'arsenals' => $state->arsenals(),
                'fog' => $state->fog(),
                'vision_layer' => $state->visionLayer(),
                'integrations' => $state->integrations(),
                'footsteps' => $state->footsteps(),
                'sync_revision' => $state->syncRevision(),
            ]);
        } catch (TabletopAccessDenied $exception) {
            wp_send_json_error(
                ['message' => $exception->getMessage()],
                403
            );
        } catch (Throwable $exception) {
            wp_send_json_error(
                ['message' => $exception->getMessage()],
                404
            );
        }
    }

    public function fragment(): void
    {
        $this->guard();

        try {
            $state = $this->chamber->state(
                $this->tableId(),
                get_current_user_id()
            );

            wp_send_json_success([
                'html' => $this->renderer->render($state),
                'sync_revision' => $state->syncRevision(),
            ]);
        } catch (TabletopAccessDenied $exception) {
            wp_send_json_error(
                ['message' => $exception->getMessage()],
                403
            );
        } catch (Throwable $exception) {
            wp_send_json_error(
                ['message' => $exception->getMessage()],
                404
            );
        }
    }

    public function moveToken(): void
    {
        $this->guard();

        try {
            $token = $this->movement->move(
                $this->tableId(),
                get_current_user_id(),
                sanitize_text_field(
                    (string) (
                        $_POST['token_id']
                        ?? ''
                    )
                ),
                (float) (
                    $_POST['x']
                    ?? 0
                ),
                (float) (
                    $_POST['y']
                    ?? 0
                ),
                max(
                    1,
                    (int) (
                        $_POST['revision']
                        ?? 1
                    )
                )
            );

            wp_send_json_success([
                'token' => $token->toArray(),
            ]);
        } catch (StaleTokenRevision $exception) {
            wp_send_json_error(
                ['message' => $exception->getMessage()],
                409
            );
        } catch (TabletopMovementDenied $exception) {
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

    private function guard(): void
    {
        if (! is_user_logged_in()) {
            wp_send_json_error(
                ['message' => 'Authentication required.'],
                401
            );
        }

        check_ajax_referer(
            self::NONCE_ACTION,
            'nonce'
        );
    }

    private function tableId(): string
    {
        return sanitize_text_field(
            (string) (
                $_POST['table_id']
                ?? ''
            )
        );
    }
}
