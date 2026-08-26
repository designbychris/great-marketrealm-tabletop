<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Encounters\Models;

use DateTimeImmutable;
use GreatMarketrealmTabletop\Tabletop\Encounters\Exceptions\EncounterStateException;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\TurnEconomy;
use InvalidArgumentException;

defined('ABSPATH') || exit;

final class Encounter
{
    /** @param array<int,EncounterCombatant> $combatants */
    private function __construct(
        private string $id,
        private string $tableId,
        private string $sceneId,
        private string $name,
        private string $status,
        private array $combatants,
        private int $round,
        private int $turnIndex,
        private int $revision,
        private DateTimeImmutable $createdAt,
        private TurnEconomy $turnEconomy
    ) {}

    public static function prepare(
        string $id,
        string $tableId,
        string $sceneId,
        string $name,
        DateTimeImmutable $createdAt
    ): self {
        foreach ([$id, $tableId, $sceneId, $name] as $value) {
            if (trim($value) === '') {
                throw new InvalidArgumentException(
                    'An Encounter requires an ID, Table ID, Scene ID and name.'
                );
            }
        }

        return new self(
            trim($id),
            trim($tableId),
            trim($sceneId),
            trim($name),
            EncounterStatus::PREPARING,
            [],
            0,
            0,
            1,
            $createdAt,
            new TurnEconomy()
        );
    }

    /** @param array<string,mixed> $record */
    public static function reconstitute(array $record): self
    {
        $combatants = [];

        foreach ((array) ($record['combatants'] ?? []) as $combatant) {
            if (is_array($combatant)) {
                $combatants[] = EncounterCombatant::reconstitute($combatant);
            }
        }

        return new self(
            trim((string) ($record['id'] ?? '')),
            trim((string) ($record['table_id'] ?? '')),
            trim((string) ($record['scene_id'] ?? '')),
            trim((string) ($record['name'] ?? '')),
            EncounterStatus::assert((string) ($record['status'] ?? '')),
            $combatants,
            max(0, (int) ($record['round'] ?? 0)),
            max(0, (int) ($record['turn_index'] ?? 0)),
            max(1, (int) ($record['revision'] ?? 1)),
            new DateTimeImmutable((string) ($record['created_at'] ?? 'now')),
            new TurnEconomy(
                is_array($record['turn_economy'] ?? null)
                    ? $record['turn_economy']
                    : []
            )
        );
    }

    public function addCombatant(EncounterCombatant $combatant): void
    {
        $this->requireStatus(
            EncounterStatus::PREPARING,
            'Combatants may only be changed while an Encounter is preparing.'
        );

        foreach ($this->combatants as $existing) {
            if ($existing->tokenId() === $combatant->tokenId()) {
                throw new EncounterStateException(
                    'That token is already participating in the Encounter.'
                );
            }
        }

        $this->combatants[] = $combatant;
        ++$this->revision;
    }

    public function start(): void
    {
        $this->requireStatus(
            EncounterStatus::PREPARING,
            'Only a preparing Encounter may begin.'
        );

        if ($this->combatants === []) {
            throw new EncounterStateException(
                'An Encounter requires at least one combatant.'
            );
        }

        usort(
            $this->combatants,
            static function (
                EncounterCombatant $left,
                EncounterCombatant $right
            ): int {
                if ($left->initiative() !== $right->initiative()) {
                    return $right->initiative() <=> $left->initiative();
                }

                if (
                    $left->initiativeModifier()
                    !== $right->initiativeModifier()
                ) {
                    return $right->initiativeModifier()
                        <=> $left->initiativeModifier();
                }

                return strcmp($left->tokenId(), $right->tokenId());
            }
        );

        $this->status = EncounterStatus::ACTIVE;
        $this->round = 1;
        $this->turnIndex = 0;
        $this->turnEconomy->reset();
        ++$this->revision;
    }

    public function pause(): void
    {
        $this->requireStatus(
            EncounterStatus::ACTIVE,
            'Only an active Encounter may be paused.'
        );

        $this->status = EncounterStatus::PAUSED;
        ++$this->revision;
    }

    public function resume(): void
    {
        $this->requireStatus(
            EncounterStatus::PAUSED,
            'Only a paused Encounter may resume.'
        );

        $this->status = EncounterStatus::ACTIVE;
        ++$this->revision;
    }

    public function advanceTurn(): void
    {
        $this->requireStatus(
            EncounterStatus::ACTIVE,
            'Turns may only advance during an active Encounter.'
        );

        ++$this->turnIndex;

        if ($this->turnIndex >= count($this->combatants)) {
            $this->turnIndex = 0;
            ++$this->round;
        }

        $this->turnEconomy->reset();
        ++$this->revision;
    }

    public function end(): void
    {
        if ($this->status === EncounterStatus::ENDED) {
            return;
        }

        $this->status = EncounterStatus::ENDED;
        ++$this->revision;
    }

    public function id(): string { return $this->id; }
    public function tableId(): string { return $this->tableId; }
    public function sceneId(): string { return $this->sceneId; }
    public function name(): string { return $this->name; }
    public function status(): string { return $this->status; }
    public function round(): int { return $this->round; }
    public function turnIndex(): int { return $this->turnIndex; }
    public function revision(): int { return $this->revision; }

    public function turnEconomy(): TurnEconomy
    {
        return $this->turnEconomy;
    }

    public function spendTurnResource(string $resource): void
    {
        $this->requireStatus(
            EncounterStatus::ACTIVE,
            'Turn resources may only be spent during an active Encounter.'
        );

        $this->turnEconomy->spend($resource);
        ++$this->revision;
    }

    /** @return array<int,EncounterCombatant> */
    public function combatants(): array
    {
        return $this->combatants;
    }

    public function currentCombatant(): ?EncounterCombatant
    {
        if (
            ! in_array(
                $this->status,
                [EncounterStatus::ACTIVE, EncounterStatus::PAUSED],
                true
            )
        ) {
            return null;
        }

        return $this->combatants[$this->turnIndex] ?? null;
    }

    public function isEnded(): bool
    {
        return $this->status === EncounterStatus::ENDED;
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'table_id' => $this->tableId,
            'scene_id' => $this->sceneId,
            'name' => $this->name,
            'status' => $this->status,
            'combatants' => array_map(
                static fn (EncounterCombatant $combatant): array =>
                    $combatant->toArray(),
                $this->combatants
            ),
            'round' => $this->round,
            'turn_index' => $this->turnIndex,
            'current_token_id' => $this->currentCombatant()?->tokenId(),
            'revision' => $this->revision,
            'created_at' => $this->createdAt->format(DATE_ATOM),
            'turn_economy' => $this->turnEconomy->toArray(),
        ];
    }

    private function requireStatus(string $status, string $message): void
    {
        if ($this->status !== $status) {
            throw new EncounterStateException($message);
        }
    }
}
