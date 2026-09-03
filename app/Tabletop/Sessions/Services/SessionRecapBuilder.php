<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Sessions\Services;

use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\BattleEventRepository;
use GreatMarketrealmTabletop\Tabletop\Chronicle\Contracts\ChamberChronicleRepository;
use GreatMarketrealmTabletop\Tabletop\Encounters\Contracts\EncounterRepository;
use GreatMarketrealmTabletop\Tabletop\Sessions\Models\SessionRecap;
use GreatMarketrealmTabletop\Tabletop\Sessions\Models\TableSession;
use DateTimeImmutable;

final class SessionRecapBuilder
{
    public function __construct(
        private BattleEventRepository $battle,
        private ChamberChronicleRepository $chamber,
        private EncounterRepository $encounters
    ) {}

    public function build(TableSession $session): SessionRecap
    {
        $facts = [];
        $seen = [];
        foreach ($this->chamber->forSession($session->tableId(), $session->id()) as $event) {
            $record = $event->toArray();
            $summary = trim((string) ($record['summary'] ?? ''));
            if ($summary !== '' && ! isset($seen[$summary])) { $facts[] = $summary; $seen[$summary] = true; }
        }
        foreach ($this->encounters->forSession($session->tableId(), $session->id()) as $encounter) {
            $facts[] = sprintf('The Fellowship faced %s%s.', $encounter->name(), $encounter->isEnded() ? ' and brought the encounter to an end' : '');
        }
        foreach ($this->battle->forSession($session->tableId(), $session->id()) as $event) {
            $record = $event->toArray();
            $type = (string) ($record['type'] ?? '');
            if ($type === 'attack-resolved') {
                $result = (string) ($record['payload']['result'] ?? '');
                $facts[] = sprintf('A battle attack was resolved%s.', $result !== '' ? ' as ' . str_replace('-', ' ', $result) : '');
            } elseif ($type === 'death-save-resolved') {
                $facts[] = 'A death save was resolved during the battle.';
            }
        }
        $facts = array_values(array_unique(array_filter($facts)));
        $body = $facts === []
            ? 'The Fellowship gathered at the Table, but no deeds were recorded in the Chronicle for this Session.'
            : implode("\n\n", array_slice($facts, 0, 12));
        return new SessionRecap($session->id(), $session->tableId(), $body, new DateTimeImmutable('now'));
    }
}
