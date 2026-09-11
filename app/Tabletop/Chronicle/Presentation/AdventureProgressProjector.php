<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Chronicle\Presentation;

use GreatMarketrealmTabletop\Tabletop\Chronicle\Models\ChamberChronicleEvent;

defined('ABSPATH') || exit;

/**
 * IV.37.3 — compares prepared Forge beats with structured adventure facts.
 */
final class AdventureProgressProjector
{
    /**
     * @param array<string,mixed> $story
     * @param array<int,ChamberChronicleEvent> $events
     * @return array<string,mixed>
     */
    public function project(array $story, array $events): array
    {
        $prepared = [];
        $statusCounts = ['pending' => 0, 'active' => 0, 'resolved' => 0];

        foreach (is_array($story['beats'] ?? null) ? $story['beats'] : [] as $index => $beat) {
            if (! is_array($beat)) {
                continue;
            }

            $status = (string) ($beat['status'] ?? 'pending');
            if (! isset($statusCounts[$status])) {
                $status = 'pending';
            }

            ++$statusCounts[$status];
            $prepared[] = [
                'index' => (int) $index,
                'stage' => trim((string) ($beat['stage'] ?? 'Adventure beat')) ?: 'Adventure beat',
                'text' => trim((string) ($beat['text'] ?? '')),
                'status' => $status,
            ];
        }

        $actual = [];
        $factCounts = [
            'secret-revealed' => 0,
            'trap-triggered' => 0,
            'treasure-looted' => 0,
            'story-beat-resolved' => 0,
        ];

        foreach ($events as $event) {
            if (! $event instanceof ChamberChronicleEvent) {
                continue;
            }

            $record = $event->toArray();
            if ((string) ($record['kind'] ?? '') !== 'adventure') {
                continue;
            }

            $action = (string) ($record['action'] ?? '');
            if (isset($factCounts[$action])) {
                ++$factCounts[$action];
            }

            $payload = is_array($record['payload']['adventure'] ?? null)
                ? $record['payload']['adventure']
                : [];

            $actual[] = [
                'id' => (string) ($record['id'] ?? ''),
                'action' => $action,
                'summary' => trim((string) ($record['summary'] ?? '')),
                'occurred_at' => (string) ($record['occurred_at'] ?? ''),
                'payload' => $payload,
            ];
        }

        return [
            'title' => trim((string) ($story['title'] ?? 'Pippin’s Adventure Notes')) ?: 'Pippin’s Adventure Notes',
            'prepared' => $prepared,
            'actual' => $actual,
            'status_counts' => $statusCounts,
            'fact_counts' => $factCounts,
        ];
    }
}
