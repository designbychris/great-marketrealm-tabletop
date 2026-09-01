<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Http;

use GreatMarketrealmTabletop\Tables\Memberships\Contracts\TableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMemberStatus;
use GreatMarketrealmTabletop\Tables\Scenes\Contracts\TableSceneRepository;
use GreatMarketrealmTabletop\Tables\Scenes\Models\GridType;
use GreatMarketrealmTabletop\Tables\Scenes\Services\TableSceneManager;
use GreatMarketrealmTabletop\Tabletop\Cartography\Contracts\DungeonForgeRepository;
use GreatMarketrealmTabletop\Tabletop\Atlas\Services\SceneShelfCleaner;
use GreatMarketrealmTabletop\Tabletop\Fog\Services\FogOfWarManager;
use GreatMarketrealmTabletop\Tabletop\Light\Contracts\EnvironmentalLightRepository;
use GreatMarketrealmTabletop\Tabletop\Light\Models\EnvironmentalLight;
use GreatMarketrealmTabletop\Tabletop\Vision\Services\VisionBarrierManager;
use RuntimeException;
use Throwable;

defined('ABSPATH') || exit;

final class DungeonForgeAjaxController
{
    /** @var array<string,array{label:string,bright:int,dim:int}> */
    private const LIGHTS = [
        'torch' => ['label' => 'Torch', 'bright' => 20, 'dim' => 20],
        'lantern' => ['label' => 'Lantern', 'bright' => 30, 'dim' => 30],
        'brazier' => ['label' => 'Brazier', 'bright' => 60, 'dim' => 60],
        'candle' => ['label' => 'Candle', 'bright' => 10, 'dim' => 10],
        'magical' => ['label' => 'Magical Light', 'bright' => 40, 'dim' => 40],
    ];

    public function __construct(
        private TableMembershipRepository $members,
        private TableSceneRepository $scenes,
        private DungeonForgeRepository $forge,
        private VisionBarrierManager $vision,
        private EnvironmentalLightRepository $lights,
        private FogOfWarManager $fog,
        private TableSceneManager $sceneManager,
        private SceneShelfCleaner $cleaner
    ) {}

    public function build(): void
    {
        $this->respond(function (string $tableId, int $userId): array {
            $sceneId = sanitize_text_field((string) ($_POST['scene_id'] ?? ''));
            if ($sceneId === '') {
                foreach ($this->scenes->forTable($tableId) as $candidate) {
                    if ($candidate->isActive()) {
                        $sceneId = $candidate->id();
                        break;
                    }
                }
            }

            $scene = $sceneId !== '' ? $this->scenes->find($tableId, $sceneId) : null;
            if ($scene === null) {
                throw new RuntimeException('Open a Scene before firing the Dungeon Forge.');
            }

            if ($this->forge->forScene($tableId, $sceneId) !== null) {
                throw new RuntimeException('This Scene already contains a forged dungeon. Create or prepare another Scene before forging again.');
            }

            $plan = $this->postedPlan();
            $projection = $this->buildPlan($tableId, $userId, $sceneId, $plan);

            return [
                'message' => sprintf(
                    'Dungeon forged · %d rooms · %d barriers · %d lights · Fog enabled.',
                    count($plan['rooms']),
                    count($projection['barrier_ids']),
                    count($projection['light_ids'])
                ),
                'forge' => $projection,
                'fog' => $projection['fog'],
            ];
        });
    }

    public function createWorld(): void
    {
        $this->respond(function (string $tableId, int $userId): array {
            $name = trim(sanitize_text_field((string) ($_POST['scene_name'] ?? '')));
            if ($name === '') {
                throw new RuntimeException('Give Pippin a name for this new place before firing the Forge.');
            }
            if (strlen($name) > 120) {
                throw new RuntimeException('That dungeon name is too long for the Keeper\'s Atlas.');
            }

            $plan = $this->postedPlan();
            $gridPixels = 64;
            $scene = $this->sceneManager->createGenerated(
                $tableId,
                $name,
                max(768, $plan['cols'] * $gridPixels),
                max(640, $plan['rows'] * $gridPixels),
                GridType::SQUARE,
                $gridPixels
            );

            try {
                $projection = $this->buildPlan($tableId, $userId, $scene->id(), $plan);
            } catch (Throwable $exception) {
                // Creation is transactional from the Keeper's point of view: a failed
                // Forge must not leave an empty or half-built Scene in the Atlas.
                $this->cleaner->clear($tableId, $scene->id());
                throw $exception;
            }

            return [
                'scene' => $scene->toArray(),
                'forge' => $projection,
                'message' => sprintf(
                    '%s has been forged into the Keeper\'s Atlas · %d rooms · %d doors · %d lights.',
                    $scene->name(),
                    count($plan['rooms']),
                    count($plan['doors']),
                    count($projection['light_ids'])
                ),
            ];
        });
    }

    /** @return array<string,mixed> */
    private function postedPlan(): array
    {
        $raw = isset($_POST['plan']) ? wp_unslash((string) $_POST['plan']) : '{}';
        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            throw new RuntimeException('The Dungeon Forge draft could not be read.');
        }
        return $this->normalisePlan($decoded);
    }

    /** @param array<string,mixed> $plan @return array<string,mixed> */
    private function buildPlan(string $tableId, int $userId, string $sceneId, array $plan): array
    {
        $created = $this->vision->addBatch($tableId, $userId, $plan['barriers'], $sceneId);

        $lightIds = [];
        foreach ($plan['lights'] as $index => $draft) {
            $preset = self::LIGHTS[$draft['kind']];
            $id = 'forge-light-' . substr(hash('sha256', $plan['seed'] . '|' . $sceneId . '|' . (string) $index), 0, 18);
            $light = new EnvironmentalLight(
                $id,
                $tableId,
                $sceneId,
                $draft['kind'],
                $preset['label'],
                $draft['x'],
                $draft['y'],
                $preset['bright'],
                $preset['dim'],
                true
            );
            $this->lights->save($light);
            $lightIds[] = $id;
        }

        // A forged dungeon starts veiled. The Keeper still owns the final reveal.
        $fog = $this->fog->configure($tableId, $userId, true, true, $sceneId);

        $projection = [
            'version' => 2,
            'seed' => $plan['seed'],
            'style' => $plan['style'],
            'theme' => $plan['theme'],
            'cols' => $plan['cols'],
            'rows' => $plan['rows'],
            'floor' => $plan['floor'],
            'rooms' => $plan['rooms'],
            'doors' => $plan['doors'],
            'lights' => $plan['lights'],
            'barrier_ids' => array_map(static fn ($barrier): string => $barrier->id(), $created),
            'light_ids' => $lightIds,
            'built_at' => gmdate(DATE_ATOM),
        ];
        $this->forge->save($tableId, $sceneId, $projection);
        $projection['fog'] = $fog;
        return $projection;
    }

    /** @param callable(string,int):array<string,mixed> $action */
    private function respond(callable $action): void
    {
        if (! is_user_logged_in()) {
            wp_send_json_error(['message' => 'Authentication required.'], 401);
        }

        check_ajax_referer(TabletopAjaxController::NONCE_ACTION, 'nonce');

        try {
            $tableId = sanitize_text_field((string) ($_POST['table_id'] ?? ''));
            $userId = get_current_user_id();
            $member = $this->members->find($tableId, $userId);

            if ($member === null || $member->status() !== TableMemberStatus::ACTIVE || ! $member->isDungeonMaster()) {
                throw new RuntimeException('Only the Dungeon Master may fire the Dungeon Forge.');
            }

            wp_send_json_success($action($tableId, $userId));
        } catch (Throwable $exception) {
            wp_send_json_error(['message' => $exception->getMessage()], 403);
        }
    }

    /** @param array<string,mixed> $plan @return array<string,mixed> */
    private function normalisePlan(array $plan): array
    {
        $seed = sanitize_text_field((string) ($plan['seed'] ?? ''));
        if ($seed === '' || strlen($seed) > 80) {
            throw new RuntimeException('The Dungeon Forge requires a short deterministic seed.');
        }

        $style = sanitize_key((string) ($plan['style'] ?? 'standard'));
        if (! in_array($style, ['compact', 'standard', 'grand'], true)) {
            $style = 'standard';
        }

        $theme = sanitize_key((string) ($plan['theme'] ?? 'pantry-stone'));
        if (! in_array($theme, ['pantry-stone', 'butcher-cellar', 'rootland-cavern', 'frostreem-vault', 'bakery-crypt', 'mushroom-grotto'], true)) {
            $theme = 'pantry-stone';
        }

        $cols = max(12, min(48, (int) ($plan['cols'] ?? 30)));
        $rows = max(10, min(36, (int) ($plan['rows'] ?? 22)));

        $floor = [];
        foreach (is_array($plan['floor'] ?? null) ? $plan['floor'] : [] as $cell) {
            if (! is_array($cell)) continue;
            $x = (int) ($cell['x'] ?? -1);
            $y = (int) ($cell['y'] ?? -1);
            if ($x < 0 || $x >= $cols || $y < 0 || $y >= $rows) continue;
            $floor[$x . ':' . $y] = ['x' => $x, 'y' => $y];
            if (count($floor) > 1200) {
                throw new RuntimeException('That Dungeon Forge floor plan is too large for one Scene.');
            }
        }
        if (count($floor) < 18) {
            throw new RuntimeException('The Dungeon Forge draft does not contain enough playable floor.');
        }

        $rooms = [];
        foreach (is_array($plan['rooms'] ?? null) ? $plan['rooms'] : [] as $room) {
            if (! is_array($room)) continue;
            $x = max(0, min($cols - 1, (int) ($room['x'] ?? 0)));
            $y = max(0, min($rows - 1, (int) ($room['y'] ?? 0)));
            $w = max(2, min($cols - $x, (int) ($room['w'] ?? 2)));
            $h = max(2, min($rows - $y, (int) ($room['h'] ?? 2)));
            $rooms[] = ['x' => $x, 'y' => $y, 'w' => $w, 'h' => $h];
            if (count($rooms) > 24) break;
        }
        if (count($rooms) < 3) {
            throw new RuntimeException('The Dungeon Forge requires at least three connected rooms.');
        }

        $barriers = [];
        foreach (is_array($plan['barriers'] ?? null) ? $plan['barriers'] : [] as $barrier) {
            if (! is_array($barrier)) continue;
            $type = sanitize_key((string) ($barrier['type'] ?? 'wall'));
            if (! in_array($type, ['wall', 'door'], true)) continue;
            $barriers[] = [
                'type' => $type,
                'x1' => $this->clamp((float) ($barrier['x1'] ?? 0)),
                'y1' => $this->clamp((float) ($barrier['y1'] ?? 0)),
                'x2' => $this->clamp((float) ($barrier['x2'] ?? 0)),
                'y2' => $this->clamp((float) ($barrier['y2'] ?? 0)),
            ];
            if (count($barriers) > 200) {
                throw new RuntimeException('That Dungeon Forge draft contains more than 200 reviewable wall/door objects.');
            }
        }
        if ($barriers === []) {
            throw new RuntimeException('The Dungeon Forge draft does not contain playable walls.');
        }

        $doors = [];
        foreach (is_array($plan['doors'] ?? null) ? $plan['doors'] : [] as $door) {
            if (! is_array($door)) continue;
            $doors[] = [
                'x1' => $this->clamp((float) ($door['x1'] ?? 0)),
                'y1' => $this->clamp((float) ($door['y1'] ?? 0)),
                'x2' => $this->clamp((float) ($door['x2'] ?? 0)),
                'y2' => $this->clamp((float) ($door['y2'] ?? 0)),
            ];
            if (count($doors) >= 24) break;
        }

        $lights = [];
        foreach (is_array($plan['lights'] ?? null) ? $plan['lights'] : [] as $light) {
            if (! is_array($light)) continue;
            $kind = sanitize_key((string) ($light['kind'] ?? 'torch'));
            if (! isset(self::LIGHTS[$kind])) continue;
            $lights[] = [
                'kind' => $kind,
                'x' => $this->clamp((float) ($light['x'] ?? 0)),
                'y' => $this->clamp((float) ($light['y'] ?? 0)),
            ];
            if (count($lights) >= 20) break;
        }

        $floor = array_values($floor);
        return compact('seed', 'style', 'theme', 'cols', 'rows', 'floor', 'rooms', 'barriers', 'doors', 'lights');
    }

    private function clamp(float $value): float
    {
        return max(0.0, min(1.0, $value));
    }
}
