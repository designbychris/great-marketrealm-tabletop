<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Sessions\Services;

use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\BattleEventRepository;
use GreatMarketrealmTabletop\Tabletop\Chronicle\Contracts\ChamberChronicleRepository;
use GreatMarketrealmTabletop\Tabletop\Encounters\Contracts\EncounterRepository;
use GreatMarketrealmTabletop\Tabletop\Sessions\Models\SessionRecap;
use GreatMarketrealmTabletop\Tabletop\Sessions\Models\TableSession;
use DateTimeImmutable;
use GreatMarketrealmTabletop\Tables\Tokens\Contracts\TableTokenRepository;

final class SessionRecapBuilder
{
    public function __construct(
        private BattleEventRepository $battle,
        private ChamberChronicleRepository $chamber,
        private EncounterRepository $encounters,
        private ?TableTokenRepository $tokens = null
    ) {}

    public function build(TableSession $session): SessionRecap
    {
        $facts = [];
        $seen = [];
        $contributions = [];
        foreach ($this->chamber->forSession($session->tableId(), $session->id()) as $event) {
            $record = $event->toArray();
            $summary = trim((string) ($record['summary'] ?? ''));
            $name = trim((string) ($record['character_name'] ?? 'Adventurer')) ?: 'Adventurer';
            if ($summary !== '' && ! isset($seen[$summary])) { $facts[] = $summary; $seen[$summary] = true; }
            if ($summary !== '') { $contributions[$name][] = $summary; }
        }
        foreach ($this->encounters->forSession($session->tableId(), $session->id()) as $encounter) {
            $facts[] = sprintf('The Fellowship faced %s%s.', $encounter->name(), $encounter->isEnded() ? ' and brought the encounter to an end' : '');
        }
        foreach ($this->battle->forSession($session->tableId(), $session->id()) as $event) {
            $record = $event->toArray();
            $type = (string) ($record['type'] ?? '');
            $tokenId = (string) ($record['token_id'] ?? '');
            $name = $this->tokens?->find($session->tableId(), $tokenId)?->label() ?? 'An adventurer';
            $summary = '';
            if ($type === 'attack-resolved') {
                $result = (string) ($record['payload']['result'] ?? '');
                $summary = sprintf('%s resolved an attack%s.', $name, $result !== '' ? ' as ' . str_replace('-', ' ', $result) : '');
                $facts[] = $summary;
            } elseif ($type === 'death-save-resolved') {
                $summary = sprintf('%s resolved a death save during the battle.', $name);
                $facts[] = $summary;
            }
            if ($summary !== '') { $contributions[$name][] = $summary; }
        }
        $facts = array_values(array_unique(array_filter($facts)));
        $body = $facts === []
            ? 'The Fellowship gathered at the Table, but no deeds were recorded in the Chronicle for this Session.'
            : implode("\n\n", array_slice($facts, 0, 12));
        $rows = [];
        foreach ($contributions as $name => $deeds) {
            $rows[] = ['character_name' => $name, 'deeds' => array_values(array_unique(array_filter($deeds)))];
        }
        return new SessionRecap($session->id(), $session->tableId(), $body, new DateTimeImmutable('now'), false, $rows);
    }
}
