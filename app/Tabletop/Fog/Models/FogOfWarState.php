<?php
declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Fog\Models;

defined('ABSPATH') || exit;

final class FogOfWarState
{
    /** @param array<int,string> $explored */
    public function __construct(
        private string $sceneId,
        private bool $enabled = false,
        private array $explored = []
    ) {}

    public function sceneId(): string { return $this->sceneId; }
    public function enabled(): bool { return $this->enabled; }

    /** @return array<int,string> */
    public function explored(): array { return $this->explored; }

    public function enable(): void { $this->enabled = true; }
    public function disable(): void { $this->enabled = false; }
    public function clear(): void { $this->explored = []; }

    /** @param array<int,string> $cells */
    public function reveal(array $cells): void
    {
        $this->explored = array_values(array_unique(array_merge(
            $this->explored,
            array_filter(array_map('strval', $cells))
        )));
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return [
            'scene_id' => $this->sceneId,
            'enabled' => $this->enabled,
            'explored' => $this->explored,
        ];
    }

    /** @param array<string,mixed> $record */
    public static function reconstitute(array $record): self
    {
        return new self(
            (string) ($record['scene_id'] ?? ''),
            ! empty($record['enabled']),
            is_array($record['explored'] ?? null)
                ? array_map('strval', $record['explored'])
                : []
        );
    }
}
