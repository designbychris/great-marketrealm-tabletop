<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Sessions\Services;

use GreatMarketrealmTabletop\Tables\Contracts\TableClock;
use GreatMarketrealmTabletop\Tables\Contracts\TableRepository;
use GreatMarketrealmTabletop\Tabletop\Sessions\Contracts\TableSessionRepository;
use GreatMarketrealmTabletop\Tabletop\Sessions\Exceptions\SessionControlDenied;
use GreatMarketrealmTabletop\Tabletop\Sessions\Models\TableSession;
use GreatMarketrealmTabletop\Tabletop\Sessions\Models\TableSessionStatus;
use RuntimeException;

defined('ABSPATH') || exit;

final class TableSessionManager
{
    public function __construct(
        private TableRepository $tables,
        private TableSessionRepository $sessions,
        private TableClock $clock
    ) {}

    public function start(string $tableId, int $userId, string $title = ''): TableSession
    {
        $this->assertKeeper($tableId, $userId);

        if ($this->sessions->currentForTable($tableId) !== null) {
            throw new RuntimeException('A Session is already in progress at this Table.');
        }

        $number = 1;
        foreach ($this->sessions->forTable($tableId) as $past) {
            $number = max($number, $past->number() + 1);
        }

        $title = trim($title);
        if ($title === '') {
            $title = 'Session ' . $number;
        }

        $session = new TableSession(
            bin2hex(random_bytes(12)),
            $tableId,
            $number,
            $title,
            TableSessionStatus::ACTIVE,
            $this->clock->now()
        );
        $this->sessions->save($session);
        return $session;
    }

    public function end(string $tableId, int $userId): TableSession
    {
        $this->assertKeeper($tableId, $userId);
        $session = $this->sessions->currentForTable($tableId);

        if ($session === null) {
            throw new RuntimeException('There is no active Session to end.');
        }

        $session->end($this->clock->now());
        $this->sessions->save($session);
        return $session;
    }

    private function assertKeeper(string $tableId, int $userId): void
    {
        $table = $this->tables->find($tableId);
        if ($table === null) {
            throw new SessionControlDenied('That Tabletop could not be found.');
        }
        if ($userId < 1 || $table->dungeonMasterUserId() !== $userId) {
            throw new SessionControlDenied('Only this Tabletop\'s Dungeon Master may control its Sessions.');
        }
    }
}
