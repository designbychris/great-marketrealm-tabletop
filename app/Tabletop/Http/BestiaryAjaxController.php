<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Http;

use GreatMarketrealmTabletop\Tabletop\Bestiary\Exceptions\BestiaryDeploymentDenied;
use GreatMarketrealmTabletop\Tabletop\Bestiary\Services\BestiaryDeploymentManager;
use Throwable;

defined('ABSPATH') || exit;

final class BestiaryAjaxController
{
    public function __construct(private BestiaryDeploymentManager $deployments) {}

    public function deployAtPoint(): void
    {
        $this->respond(function (): array {
            $tokens = $this->deployments->deployAtPoint(
                $this->tableId(),
                get_current_user_id(),
                $this->sceneId(),
                sanitize_key((string) ($_POST['creature_id'] ?? '')),
                (float) ($_POST['x'] ?? 0),
                (float) ($_POST['y'] ?? 0),
                $this->quantity(),
                ! empty($_POST['hidden'])
            );

            return $this->payload($tokens, 'Creature summoned to the Scene.');
        });
    }

    public function deployAtThreshold(): void
    {
        $this->respond(function (): array {
            $tokens = $this->deployments->deployAtMonsterThreshold(
                $this->tableId(),
                get_current_user_id(),
                $this->sceneId(),
                sanitize_key((string) ($_POST['creature_id'] ?? '')),
                $this->quantity(),
                ! empty($_POST['hidden'])
            );

            return $this->payload($tokens, 'Creature summoned at the Monster Deployment Threshold.');
        });
    }

    private function payload(array $tokens, string $message): array
    {
        return [
            'tokens' => array_map(
                static fn ($token): array => $token->toArray(),
                $tokens
            ),
            'count' => count($tokens),
            'message' => count($tokens) === 1
                ? $message
                : count($tokens) . ' creatures summoned to the Scene.',
        ];
    }

    private function quantity(): int
    {
        return max(1, min(12, absint($_POST['quantity'] ?? 1)));
    }

    private function respond(callable $action): void
    {
        if (! is_user_logged_in()) {
            wp_send_json_error(['message' => 'Authentication required.'], 401);
        }

        check_ajax_referer(TabletopAjaxController::NONCE_ACTION, 'nonce');

        try {
            wp_send_json_success($action());
        } catch (BestiaryDeploymentDenied $exception) {
            wp_send_json_error(['message' => $exception->getMessage()], 403);
        } catch (Throwable $exception) {
            wp_send_json_error(['message' => $exception->getMessage()], 400);
        }
    }

    private function tableId(): string
    {
        return sanitize_text_field((string) ($_POST['table_id'] ?? ''));
    }

    private function sceneId(): string
    {
        return sanitize_text_field((string) ($_POST['scene_id'] ?? ''));
    }
}
