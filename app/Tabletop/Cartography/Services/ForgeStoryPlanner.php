<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Cartography\Services;

defined('ABSPATH') || exit;

/**
 * Turns the Forge's semantic preparation into a deterministic Keeper adventure brief.
 */
final class ForgeStoryPlanner
{
    /** @param array<string,mixed> $plan @param array<string,mixed> $context @return array<string,mixed> */
    public function plan(array $plan, array $context = []): array
    {
        if (($plan['scene_type'] ?? 'dungeon') !== 'dungeon' || empty($plan['include_story'])) return [];

        $seed = (string) ($plan['seed'] ?? 'Peppercorn');
        $theme = trim((string) ($plan['theme'] ?? 'forgotten stores')) ?: 'forgotten stores';
        $rooms = is_array($plan['rooms'] ?? null) ? $plan['rooms'] : [];
        $secrets = is_array($context['secrets'] ?? null) ? $context['secrets'] : [];
        $traps = is_array($context['traps'] ?? null) ? $context['traps'] : [];
        $treasure = is_array($context['treasure'] ?? null) ? $context['treasure'] : [];
        $occupants = is_array($context['occupants'] ?? null) ? $context['occupants'] : [];
        $hasLair = false;
        foreach ($rooms as $room) if (is_array($room) && ($room['role'] ?? '') === 'lair' && ! empty($room['boss_lair'])) { $hasLair = true; break; }
        $boss = trim((string) ($plan['lair_occupant_id'] ?? ''));

        $titles = ['The Ledger Beneath ' . ucfirst($theme), 'The Curious Case of ' . ucfirst($theme), 'Pippin and the ' . ucfirst($theme) . ' Detour'];
        $hooks = [
            'Something valuable entered this place and never came back out.',
            'The old route has reopened, but the signs suggest somebody arrived first.',
            'A routine survey has uncovered evidence that this dungeon is still very much in use.',
        ];
        $title = $titles[$this->index($seed, 'title', count($titles))];
        $hook = $hooks[$this->index($seed, 'hook', count($hooks))];

        $beats = [];
        $beats[] = ['stage' => 'Arrival', 'text' => ! empty($plan['entry_anchor']) ? 'The party crosses the marked threshold and enters under the Keeper’s veil.' : 'The party enters cautiously; no formal arrival threshold has been prepared.'];
        if ($occupants !== []) $beats[] = ['stage' => 'Pressure', 'text' => count($occupants) . ' ordinary dungeon occupant group' . (count($occupants) === 1 ? '' : 's') . ' turn exploration into contested ground.'];
        if ($secrets !== []) $beats[] = ['stage' => 'Discovery', 'text' => count($secrets) . ' concealed secret' . (count($secrets) === 1 ? '' : 's') . ' reward players who investigate rather than simply advance.'];
        if ($traps !== []) $beats[] = ['stage' => 'Complication', 'text' => count($traps) . ' prepared trap' . (count($traps) === 1 ? '' : 's') . ' make the route itself part of the encounter.'];
        if ($hasLair) $beats[] = ['stage' => 'Climax', 'text' => $boss !== '' ? 'The route culminates in the Boss Lair, where the prepared lair occupant waits.' : 'The route culminates in a Boss Lair whose final occupant is left to the Keeper.'];
        if ($treasure !== []) $beats[] = ['stage' => 'Reward', 'text' => count($treasure) . ' treasure cache' . (count($treasure) === 1 ? '' : 's') . ' give exploration a tangible payoff.'];
        if (count($beats) === 1) $beats[] = ['stage' => 'Exploration', 'text' => 'The dungeon is deliberately sparse: let its rooms, doors and furnishings carry the session.'];

        return [
            'version' => 1,
            'title' => $title,
            'hook' => $hook,
            'theme' => $theme,
            'beats' => $beats,
            'keeper_note' => 'Pippin’s route: establish the entrance, let clues and hazards build pressure, then pay off the strongest prepared room.',
            'summary' => sprintf('%d rooms · %d occupants · %d secrets · %d traps · %d treasure caches%s', count($rooms), count($occupants), count($secrets), count($traps), count($treasure), $hasLair ? ' · Boss Lair' : ''),
        ];
    }

    private function index(string $seed, string $salt, int $count): int
    {
        if ($count <= 1) return 0;
        return (int) (hexdec(substr(hash('sha256', $seed . '|' . $salt), 0, 8)) % $count);
    }
}
