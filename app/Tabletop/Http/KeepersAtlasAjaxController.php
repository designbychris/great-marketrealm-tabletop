<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Http;

defined('ABSPATH') || exit;

use GreatMarketrealmTabletop\Tabletop\Atlas\Services\KeepersAtlas;
use GreatMarketrealmTabletop\Tabletop\Atlas\Transitions\Repositories\WordPressSceneTransitionRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Contracts\TableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMemberStatus;
use GreatMarketrealmTabletop\Tables\Scenes\Contracts\TableSceneRepository;
use GreatMarketrealmTabletop\Tabletop\Atlas\Exceptions\AtlasDenied;
use Throwable;

final class KeepersAtlasAjaxController
{
    public function __construct(
        private KeepersAtlas $atlas,
        private TableMembershipRepository $members,
        private TableSceneRepository $scenes,
        private WordPressSceneTransitionRepository $transitions
    ) {}

    public function addMap(): void
    {
        $this->respond(function (): array {
            $scene = $this->atlas->addMap(
                $this->tableId(),
                get_current_user_id(),
                sanitize_text_field(wp_unslash((string) ($_POST['scene_name'] ?? ''))),
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

    public function arriveAtThreshold(): void
    {
        $this->respond(function (): array {
            $token = $this->atlas->arriveAtThreshold(
                $this->tableId(),
                get_current_user_id(),
                sanitize_text_field((string) ($_POST['scene_id'] ?? ''))
            );

            return [
                'created' => $token !== null,
                'token' => $token?->toArray(),
                'message' => $token !== null
                    ? 'Your adventurer crosses the Party Arrival Threshold.'
                    : 'The Scene remembers your adventurer already.',
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

    public function moveThreshold(): void
    {
        $this->respond(function (): array {
            $marker = $this->atlas->moveThreshold(
                $this->tableId(),
                get_current_user_id(),
                sanitize_text_field((string) ($_POST['scene_id'] ?? '')),
                sanitize_text_field((string) ($_POST['marker_id'] ?? '')),
                (float) ($_POST['x'] ?? 0),
                (float) ($_POST['y'] ?? 0)
            );

            return [
                'marker' => $marker->toArray(),
                'message' => $marker->type() === 'party'
                    ? 'Party Arrival Threshold repositioned.'
                    : 'Monster Deployment Threshold repositioned.',
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


    public function transitionStatus(): void
    {
        $this->respond(function (): array {
            $tableId = $this->tableId();
            $sourceSceneId = sanitize_text_field((string) ($_POST['source_scene_id'] ?? ''));
            $this->assertDungeonMaster($tableId);
            return ['transition' => $this->transitions->forSource($tableId, $sourceSceneId)];
        });
    }

    public function linkTransition(): void
    {
        $this->respond(function (): array {
            $tableId = $this->tableId();
            $sourceSceneId = sanitize_text_field((string) ($_POST['source_scene_id'] ?? ''));
            $destinationSceneId = sanitize_text_field((string) ($_POST['destination_scene_id'] ?? ''));
            $this->assertDungeonMaster($tableId);

            if ($sourceSceneId === '' || $destinationSceneId === '' || $sourceSceneId === $destinationSceneId) {
                throw new AtlasDenied('Choose another Scene as the destination.');
            }
            $source = $this->scenes->find($tableId, $sourceSceneId);
            $destination = $this->scenes->find($tableId, $destinationSceneId);
            if ($source === null || $destination === null) {
                throw new AtlasDenied('Both ends of the route must belong to this Table.');
            }

            $transition = [
                'source_scene_id' => $sourceSceneId,
                'destination_scene_id' => $destinationSceneId,
                'destination_anchor' => 'party',
                'keeper_controlled' => true,
                'updated_at' => gmdate(DATE_ATOM),
            ];
            $this->transitions->save($tableId, $sourceSceneId, $transition);

            return [
                'transition' => $transition,
                'message' => 'Pippin has drawn the way to ' . $destination->name() . '.',
            ];
        });
    }

    public function removeTransition(): void
    {
        $this->respond(function (): array {
            $tableId = $this->tableId();
            $sourceSceneId = sanitize_text_field((string) ($_POST['source_scene_id'] ?? ''));
            $this->assertDungeonMaster($tableId);
            $this->transitions->remove($tableId, $sourceSceneId);
            return ['message' => 'The Scene route has been erased.'];
        });
    }

    public function travelTransition(): void
    {
        $this->respond(function (): array {
            $tableId = $this->tableId();
            $sourceSceneId = sanitize_text_field((string) ($_POST['source_scene_id'] ?? ''));
            $this->assertDungeonMaster($tableId);
            $transition = $this->transitions->forSource($tableId, $sourceSceneId);
            if ($transition === null) {
                throw new AtlasDenied('This way does not lead anywhere yet.');
            }
            $destinationSceneId = sanitize_text_field((string) ($transition['destination_scene_id'] ?? ''));
            $scene = $this->atlas->openMap($tableId, get_current_user_id(), $destinationSceneId);
            return [
                'scene' => $scene->toArray(),
                'destination_anchor' => 'party',
                'message' => 'The party travels to ' . $scene->name() . '.',
            ];
        });
    }

    private function assertDungeonMaster(string $tableId): void
    {
        $member = $this->members->find($tableId, get_current_user_id());
        if ($member === null || $member->status() !== TableMemberStatus::ACTIVE || ! $member->isDungeonMaster()) {
            throw new AtlasDenied('Only the Dungeon Master may redraw the roads between Scenes.');
        }
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
