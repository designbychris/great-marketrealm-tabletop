<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Http;

use GreatMarketrealmTabletop\Tabletop\Encounters\Exceptions\EncounterControlDenied;
use GreatMarketrealmTabletop\Tabletop\Encounters\Exceptions\EncounterStateException;
use GreatMarketrealmTabletop\Tabletop\Encounters\Exceptions\StaleEncounterRevision;
use GreatMarketrealmTabletop\Tabletop\Encounters\Services\EncounterManager;
use Throwable;

defined('ABSPATH') || exit;

final class EncounterAjaxController
{
    public function __construct(
        private EncounterManager $encounters
    ) {}

    public function prepare(): void
    {
        $this->guard();

        $this->respond(fn () => $this->encounters->prepare(
            $this->tableId(),
            get_current_user_id(),
            sanitize_text_field((string) ($_POST['scene_id'] ?? '')),
            sanitize_text_field((string) ($_POST['name'] ?? 'Encounter'))
        ));
    }

    public function addCombatant(): void
    {
        $this->guard();

        $this->respond(fn () => $this->encounters->addCombatant(
            $this->tableId(),
            get_current_user_id(),
            $this->encounterId(),
            sanitize_text_field((string) ($_POST['token_id'] ?? '')),
            (int) ($_POST['initiative'] ?? 0),
            (int) ($_POST['initiative_modifier'] ?? 0),
            $this->revision()
        ));
    }

    public function start(): void { $this->control('start'); }
    public function pause(): void { $this->control('pause'); }
    public function resume(): void { $this->control('resume'); }
    public function advance(): void { $this->control('advance'); }
    public function end(): void { $this->control('end'); }

    private function control(string $operation): void
    {
        $this->guard();

        $this->respond(fn () => $this->encounters->{$operation}(
            $this->tableId(),
            get_current_user_id(),
            $this->encounterId(),
            $this->revision()
        ));
    }

    private function respond(callable $callback): void
    {
        try {
            $encounter = $callback();

            wp_send_json_success([
                'encounter' => $encounter->toArray(),
            ]);
        } catch (StaleEncounterRevision $exception) {
            wp_send_json_error(['message' => $exception->getMessage()], 409);
        } catch (EncounterControlDenied $exception) {
            wp_send_json_error(['message' => $exception->getMessage()], 403);
        } catch (EncounterStateException $exception) {
            wp_send_json_error(['message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            wp_send_json_error(['message' => $exception->getMessage()], 400);
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
            TabletopAjaxController::NONCE_ACTION,
            'nonce'
        );
    }

    private function tableId(): string
    {
        return sanitize_text_field((string) ($_POST['table_id'] ?? ''));
    }

    private function encounterId(): string
    {
        return sanitize_text_field((string) ($_POST['encounter_id'] ?? ''));
    }

    private function revision(): int
    {
        return max(1, (int) ($_POST['revision'] ?? 1));
    }
}
