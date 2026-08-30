<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Http;

use GreatMarketrealmTabletop\Tabletop\Vision\Services\VisionBarrierManager;
use Throwable;

defined('ABSPATH') || exit;

final class CartographyAssistantAjaxController
{
    public function __construct(private VisionBarrierManager $vision) {}

    public function apply(): void
    {
        if (! is_user_logged_in()) {
            wp_send_json_error(['message' => 'Authentication required.'], 401);
        }

        check_ajax_referer(TabletopAjaxController::NONCE_ACTION, 'nonce');

        try {
            $raw = isset($_POST['suggestions'])
                ? wp_unslash((string) $_POST['suggestions'])
                : '[]';
            $decoded = json_decode($raw, true);

            if (! is_array($decoded)) {
                throw new \RuntimeException(
                    'The Cartography Assistant draft could not be read.'
                );
            }

            $barriers = $this->vision->addBatch(
                sanitize_text_field((string) ($_POST['table_id'] ?? '')),
                get_current_user_id(),
                $decoded,
                sanitize_text_field((string) ($_POST['scene_id'] ?? ''))
            );

            wp_send_json_success([
                'applied' => count($barriers),
                'barriers' => array_map(
                    static fn ($barrier): array => $barrier->toArray(),
                    $barriers
                ),
            ]);
        } catch (Throwable $exception) {
            wp_send_json_error([
                'message' => $exception->getMessage(),
            ], 403);
        }
    }
}
