<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Http;

defined('ABSPATH') || exit;

use GreatMarketrealmTabletop\Tabletop\Atlas\Exceptions\AtlasDenied;
use GreatMarketrealmTabletop\Tabletop\Atlas\Services\KeepersAtlas;
use Throwable;

final class KeepersAtlasAjaxController
{
    public function __construct(private KeepersAtlas $atlas) {}

    public function addMap(): void
    {
        $this->respond(function (): array {
            $scene = $this->atlas->addMap(
                $this->tableId(),
                get_current_user_id(),
                sanitize_text_field((string) ($_POST['scene_name'] ?? '')),
                absint($_POST['attachment_id'] ?? 0),
                max(1, absint($_POST['grid_size'] ?? 64))
            );

            return [
                'scene' => $scene->toArray(),
                'message' => $scene->name() . ' has been entered into the Keeper\'s Atlas.',
            ];
        });
    }

    public function openMap(): void
    {
        $this->respond(function (): array {
            $scene = $this->atlas->openMap(
                $this->tableId(),
                get_current_user_id(),
                sanitize_text_field((string) ($_POST['scene_id'] ?? ''))
            );

            return [
                'scene' => $scene->toArray(),
                'message' => $scene->name() . ' is now the active Scene.',
            ];
        });
    }

    public function placeThreshold(): void
    {
        $this->respond(function (): array {
            $marker = $this->atlas->placeThreshold(
                $this->tableId(),
                get_current_user_id(),
                sanitize_text_field((string) ($_POST['scene_id'] ?? '')),
                sanitize_key((string) ($_POST['threshold_type'] ?? '')),
                (float) ($_POST['x'] ?? 0),
                (float) ($_POST['y'] ?? 0)
            );
            return [
                'marker' => $marker->toArray(),
                'message' => $marker->type() === 'party'
                    ? 'Party Arrival Threshold placed.'
                    : 'Monster Deployment Threshold placed.',
            ];
        });
    }

    public function removeThreshold(): void
    {
        $this->respond(function (): array {
            $this->atlas->removeThreshold(
                $this->tableId(),
                get_current_user_id(),
                sanitize_text_field((string) ($_POST['scene_id'] ?? '')),
                sanitize_text_field((string) ($_POST['marker_id'] ?? ''))
            );
            return ['message' => 'Threshold Marker removed.'];
        });
    }

    public function deleteMap(): void
    {
        $this->respond(function (): array {
            $name = $this->atlas->deleteMap(
                $this->tableId(),
                get_current_user_id(),
                sanitize_text_field((string) ($_POST['scene_id'] ?? ''))
            );
            return [
                'message' => $name . ' has been cleared from the Keeper\'s Atlas.',
            ];
        });
    }

    private function respond(callable $action): void
    {
        if (! is_user_logged_in()) {
            wp_send_json_error(['message' => 'Authentication required.'], 401);
        }

        check_ajax_referer(TabletopAjaxController::NONCE_ACTION, 'nonce');

        try {
            wp_send_json_success($action());
        } catch (AtlasDenied $exception) {
            wp_send_json_error(['message' => $exception->getMessage()], 403);
        } catch (Throwable $exception) {
            wp_send_json_error(['message' => $exception->getMessage()], 400);
        }
    }

    private function tableId(): string
    {
        return sanitize_text_field((string) ($_POST['table_id'] ?? ''));
    }
}
