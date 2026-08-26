<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Models;

defined('ABSPATH') || exit;

final class DeathSaveState
{
    public function __construct(
        private string $tokenId,
        private int $successes = 0,
        private int $failures = 0,
        private bool $stable = false
    ) {}

    public function tokenId(): string
    {
        return $this->tokenId;
    }

    public function successes(): int
    {
        return $this->successes;
    }

    public function failures(): int
    {
        return $this->failures;
    }

    public function stable(): bool
    {
        return $this->stable;
    }

    public function dead(): bool
    {
        return $this->failures >= 3;
    }

    public function resolved(): bool
    {
        return $this->stable || $this->dead();
    }

    public function recordSuccess(int $amount = 1): void
    {
        if ($this->resolved()) {
            return;
        }

        $this->successes = min(3, $this->successes + $amount);

        if ($this->successes >= 3) {
            $this->stable = true;
        }
    }

    public function recordFailure(int $amount = 1): void
    {
        if ($this->resolved()) {
            return;
        }

        $this->failures = min(3, $this->failures + $amount);
    }

    public function stabilize(): void
    {
        if (! $this->dead()) {
            $this->stable = true;
        }
    }

    public function reset(): void
    {
        $this->successes = 0;
        $this->failures = 0;
        $this->stable = false;
    }

    /** @return array<string,int|string|bool> */
    public function toArray(): array
    {
        return [
            'token_id' => $this->tokenId,
            'successes' => $this->successes,
            'failures' => $this->failures,
            'stable' => $this->stable,
            'dead' => $this->dead(),
            'resolved' => $this->resolved(),
        ];
    }

    /** @param array<string,mixed> $record */
    public static function reconstitute(array $record): self
    {
        return new self(
            (string) ($record['token_id'] ?? ''),
            max(0, min(3, (int) ($record['successes'] ?? 0))),
            max(0, min(3, (int) ($record['failures'] ?? 0))),
            (bool) ($record['stable'] ?? false)
        );
    }
}
