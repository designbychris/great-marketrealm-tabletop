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
                get_current_user_id(),
                $this->sceneId()
            );

            $integrations = $this->visibleIntegrations(
                $state->integrations(),
                $state->isDungeonMaster()
            );

            wp_send_json_success([
                'table' => $state->table(),
                'viewer' => $state->viewer(),
                'members' => $state->members(),
                'scene' => $state->scene(),
                'scenes' => $state->scenes(),
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
                'integrations' => $integrations,
                'forge_revision' => $this->forgeRevision($integrations),
                'footsteps' => $state->footsteps(),
                'preparation' => $state->preparation(),
                'thresholds' => $state->thresholds(),
                'bestiary' => $state->bestiary(),
                'session' => $state->session(),
                'session_recap' => $state->sessionRecap(),
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
                get_current_user_id(),
                $this->sceneId()
            );

            $integrations = $this->visibleIntegrations(
                $state->integrations(),
                $state->isDungeonMaster()
            );

            wp_send_json_success([
                'html' => $this->renderer->render($state),
                'sync_revision' => $state->syncRevision(),
                'forge_revision' => $this->forgeRevision($integrations),
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
                ),
                $this->sceneId()
            );

            wp_send_json_success([
                'token' => $token->toArray(),
                'trap' => $this->movement->lastTrapEvent(),
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

    /**
     * Keep Keeper-only Forge preparation out of Player AJAX state.
     *
     * @param array<string,mixed> $integrations
     * @return array<string,mixed>
     */
    private function visibleIntegrations(array $integrations, bool $isDungeonMaster): array
    {
        if ($isDungeonMaster) {
            return $integrations;
        }

        $forge = is_array($integrations['dungeon_forge'] ?? null)
            ? $integrations['dungeon_forge']
            : [];

        if ($forge === []) {
            return $integrations;
        }

        $hiddenDoorIndexes = [];
        $visibleSecrets = [];
        foreach (is_array($forge['secrets'] ?? null) ? $forge['secrets'] : [] as $secret) {
            if (! is_array($secret)) {
                continue;
            }
            if (! empty($secret['revealed'])) {
                $visibleSecrets[] = $secret;
                continue;
            }
            if (($secret['kind'] ?? '') === 'secret-door') {
                $hiddenDoorIndexes[] = (int) ($secret['door_index'] ?? -1);
            }
        }

        if ($hiddenDoorIndexes !== []) {
            $forge['doors'] = array_values(array_filter(
                is_array($forge['doors'] ?? null) ? $forge['doors'] : [],
                static fn ($door, $index): bool => ! in_array((int) $index, $hiddenDoorIndexes, true),
                ARRAY_FILTER_USE_BOTH
            ));
        }

        $forge['secrets'] = $visibleSecrets;
        $forge['traps'] = array_values(array_filter(
            is_array($forge['traps'] ?? null) ? $forge['traps'] : [],
            static fn ($trap): bool => is_array($trap) && ! empty($trap['revealed'])
        ));
        $forge['treasure'] = array_values(array_filter(
            is_array($forge['treasure'] ?? null) ? $forge['treasure'] : [],
            static fn ($treasure): bool => is_array($treasure) && ! empty($treasure['revealed'])
        ));
        $integrations['dungeon_forge'] = $forge;

        return $integrations;
    }

    /** @param array<string,mixed> $integrations */
    private function forgeRevision(array $integrations): string
    {
        $forge = is_array($integrations['dungeon_forge'] ?? null)
            ? $integrations['dungeon_forge']
            : [];

        return hash('sha256', (string) json_encode([
            'doors' => $forge['doors'] ?? [],
            'secrets' => $forge['secrets'] ?? [],
            'traps' => $forge['traps'] ?? [],
            'treasure' => $forge['treasure'] ?? [],
        ], JSON_UNESCAPED_SLASHES));
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


    private function sceneId(): string
    {
        return sanitize_text_field(
            (string) (
                $_POST['scene_id']
                ?? ''
            )
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
