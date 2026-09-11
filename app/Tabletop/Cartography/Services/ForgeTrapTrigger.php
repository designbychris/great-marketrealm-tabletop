<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Cartography\Services;

use GreatMarketrealmTabletop\Tabletop\Cartography\Contracts\DungeonForgeRepository;
use GreatMarketrealmTabletop\Tabletop\Chronicle\Services\AdventureEventRecorder;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMember;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableToken;

defined('ABSPATH') || exit;

final class ForgeTrapTrigger
{
    public function __construct(
        private DungeonForgeRepository $forge,
        private ?AdventureEventRecorder $adventureEvents = null
    ) {}

    /** @return array<string,mixed>|null */
    public function afterMovement(
        TableMember $member,
        TableToken $token,
        float $fromX,
        float $fromY
    ): ?array {
        if ($member->isDungeonMaster()) {
            return null;
        }

        $projection = $this->forge->forScene($token->tableId(), $token->sceneId());
        if (! is_array($projection)) {
            return null;
        }

        foreach (is_array($projection['traps'] ?? null) ? $projection['traps'] : [] as $index => $trap) {
            if (! is_array($trap) || empty($trap['armed']) || ! empty($trap['triggered'])) {
                continue;
            }

            $x = (float) ($trap['x'] ?? -10);
            $y = (float) ($trap['y'] ?? -10);
            $radius = max(.004, min(.12, (float) ($trap['trigger_radius'] ?? .018)));

            if (! $this->touches($fromX, $fromY, $token->x(), $token->y(), $x, $y, $radius)) {
                continue;
            }

            $projection['traps'][$index]['triggered'] = true;
            $projection['traps'][$index]['armed'] = false;
            $projection['traps'][$index]['revealed'] = true;
            $projection['traps'][$index]['triggered_by_token_id'] = $token->id();
            $projection['traps'][$index]['triggered_at'] = gmdate(DATE_ATOM);
            $this->forge->save($token->tableId(), $token->sceneId(), $projection);

            $updated = $projection['traps'][$index];
            $label = trim((string) ($updated['label'] ?? 'Trap')) ?: 'Trap';
            $this->adventureEvents?->record(
                $token->tableId(),
                $member->userId(),
                'trap-triggered',
                $label . ' was triggered.',
                [
                    'scene_id' => $token->sceneId(),
                    'trap_id' => (string) ($updated['id'] ?? ''),
                    'trap_type' => (string) ($updated['trap_type'] ?? ''),
                    'token_id' => $token->id(),
                    'source' => 'movement',
                ]
            );

            return $updated;
        }

        return null;
    }

    private function touches(
        float $ax,
        float $ay,
        float $bx,
        float $by,
        float $px,
        float $py,
        float $radius
    ): bool {
        $dx = $bx - $ax;
        $dy = $by - $ay;
        $length = $dx * $dx + $dy * $dy;

        if ($length <= .0000001) {
            return hypot($px - $ax, $py - $ay) <= $radius;
        }

        $t = max(0.0, min(1.0, (($px - $ax) * $dx + ($py - $ay) * $dy) / $length));
        return hypot($px - ($ax + $t * $dx), $py - ($ay + $t * $dy)) <= $radius;
    }
}
