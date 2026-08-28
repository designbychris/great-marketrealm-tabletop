<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Presentation;

use GreatMarketrealmTabletop\Tabletop\Battle\Models\BattleEvent;

defined('ABSPATH') || exit;

final class BattleLogProjector
{
    private const MAX_ENTRIES = 12;

    /**
     * @param array<int,BattleEvent> $events
     * @param array<string,string> $tokenLabels
     * @return array<int,array<string,mixed>>
     */
    public function project(
        array $events,
        array $tokenLabels
    ): array {
        $entries = [];
        $count = count($events);

        for ($index = 0; $index < $count; ++$index) {
            $event = $events[$index];
            $record = $event->toArray();
            $actorId = (string) ($record['token_id'] ?? '');
            $isSatchelRoll = ($record['type'] ?? '') === 'satchel-roll';

            if (! $isSatchelRoll && ! isset($tokenLabels[$actorId])) {
                continue;
            }

            if (
                ($record['type'] ?? '') === 'deed-performed'
                && ($record['payload']['deed'] ?? '') === 'attack'
            ) {
                continue;
            }

            $damage = null;

            if (
                ($record['type'] ?? '') === 'attack-resolved'
                && isset($events[$index + 1])
            ) {
                $candidate = $events[$index + 1]->toArray();

                if ($this->matchesDamage(
                    $record,
                    $candidate
                )) {
                    $damage = $candidate;
                    ++$index;
                }
            }

            $summary = $this->summary(
                $record,
                $damage,
                $tokenLabels
            );

            if ($summary === null) {
                continue;
            }

            $entries[] = [
                'id' => (string) ($record['id'] ?? ''),
                'round' => (int) ($record['round'] ?? 0),
                'turn_index' => (int) (
                    $record['turn_index'] ?? 0
                ),
                'type' => (string) ($record['type'] ?? ''),
                'summary' => $summary,
                'occurred_at' => (string) (
                    $record['occurred_at'] ?? ''
                ),
            ];
        }

        return array_reverse(
            array_slice(
                $entries,
                -self::MAX_ENTRIES
            )
        );
    }

    /**
     * @param array<string,mixed> $attack
     * @param array<string,mixed> $damage
     */
    private function matchesDamage(
        array $attack,
        array $damage
    ): bool {
        return ($damage['type'] ?? '') === 'damage-applied'
            && ($attack['token_id'] ?? '')
                === ($damage['token_id'] ?? '')
            && ($attack['round'] ?? -1)
                === ($damage['round'] ?? -2)
            && ($attack['turn_index'] ?? -1)
                === ($damage['turn_index'] ?? -2);
    }

    /**
     * @param array<string,mixed> $event
     * @param array<string,mixed>|null $damage
     * @param array<string,string> $labels
     */
    private function summary(
        array $event,
        ?array $damage,
        array $labels
    ): ?string {
        $actor = $labels[
            (string) ($event['token_id'] ?? '')
        ] ?? 'Combatant';
        $payload = is_array($event['payload'] ?? null)
            ? $event['payload']
            : [];
        $type = (string) ($event['type'] ?? '');

        if ($type === 'satchel-roll') {
            $summary = trim((string) ($payload['summary'] ?? ''));
            return $summary === '' ? null : $summary . (str_ends_with($summary, '.') ? '' : '.');
        }

        if ($type === 'attack-resolved') {
            return $this->attackSummary(
                $actor,
                $payload,
                $damage,
                $labels
            );
        }

        if ($type === 'deed-performed') {
            $deed = ucfirst(
                (string) ($payload['deed'] ?? 'deed')
            );

            return sprintf(
                '%s used %s.',
                $actor,
                $deed
            );
        }

        if ($type === 'death-save-resolved') {
            $outcome = is_array(
                $payload['outcome'] ?? null
            )
                ? $payload['outcome']
                : [];
            $result = str_replace(
                '-',
                ' ',
                (string) ($outcome['result'] ?? 'resolved')
            );

            return sprintf(
                '%s rolled a death save: %s.',
                $actor,
                ucfirst($result)
            );
        }

        if (in_array(
            $type,
            [
                'condition-applied',
                'condition-removed',
                'condition-expired',
            ],
            true
        )) {
            $condition = ucfirst(
                (string) (
                    $payload['condition']
                    ?? 'condition'
                )
            );

            return match ($type) {
                'condition-applied' => sprintf(
                    '%s became %s.',
                    $actor,
                    $condition
                ),
                'condition-removed' => sprintf(
                    '%s is no longer %s.',
                    $actor,
                    $condition
                ),
                default => sprintf(
                    "%s's %s expired.",
                    $actor,
                    $condition
                ),
            };
        }

        return null;
    }

    /**
     * @param array<string,mixed> $attack
     * @param array<string,mixed>|null $damage
     * @param array<string,string> $labels
     */
    private function attackSummary(
        string $actor,
        array $attack,
        ?array $damage,
        array $labels
    ): string {
        $targetId = (string) (
            $attack['target_token_id'] ?? ''
        );
        $target = $labels[$targetId]
            ?? 'a hidden target';

        $result = match (
            (string) ($attack['result'] ?? '')
        ) {
            'critical-hit' => 'critically hit',
            'hit' => 'hit',
            'critical-miss' => 'critically missed',
            default => 'missed',
        };

        $summary = sprintf(
            '%s %s %s',
            $actor,
            $result,
            $target
        );

        if ($damage === null) {
            return $summary . '.';
        }

        $payload = is_array(
            $damage['payload'] ?? null
        )
            ? $damage['payload']
            : [];
        $adjustment = is_array(
            $payload['damage_adjustment'] ?? null
        )
            ? $payload['damage_adjustment']
            : [];
        $vitality = is_array(
            $payload['vitality'] ?? null
        )
            ? $payload['vitality']
            : [];

        $resolved = (int) (
            $adjustment['resolved_damage'] ?? 0
        );
        $damageType = strtoupper(
            (string) (
                $adjustment['damage_type']
                ?? 'damage'
            )
        );
        $effects = is_array(
            $adjustment['effects'] ?? null
        )
            ? $adjustment['effects']
            : [];

        $effect = '';

        if (in_array('immune', $effects, true)) {
            $effect = ' IMMUNE';
        } elseif (
            in_array('vulnerable', $effects, true)
        ) {
            $effect = ' WEAK';
        } elseif (
            in_array('resistant', $effects, true)
        ) {
            $effect = ' RESIST';
        }

        return sprintf(
            '%s — %d %s%s damage; %d/%d HP.',
            $summary,
            $resolved,
            $damageType,
            $effect,
            (int) ($vitality['current_hp'] ?? 0),
            (int) ($vitality['maximum_hp'] ?? 0)
        );
    }
}
