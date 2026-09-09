<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use DateTimeImmutable;
use GreatMarketrealmTabletop\Tabletop\Cartography\Contracts\DungeonForgeRepository;
use GreatMarketrealmTabletop\Tabletop\Cartography\Services\LairEncounterParticipant;
use GreatMarketrealmTabletop\Tables\Tokens\Contracts\TableTokenRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableToken;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableTokenType;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableTokenVisibility;
use PHPUnit\Framework\TestCase;

final class LairEncounterParticipantTest extends TestCase
{
    public function test_selected_forged_boss_is_revealed_at_encounter_boundary(): void
    {
        $token = $this->bossToken(TableTokenVisibility::HIDDEN);
        $tokens = new LairParticipantTokenStore($token);
        $service = new LairEncounterParticipant(
            new LairParticipantForgeStore($this->plan()),
            $tokens
        );

        $awakened = $service->awakenIfParticipating(
            'table-1',
            'scene-1',
            ['hero-token', 'boss-token']
        );

        self::assertSame('boss-token', $awakened);
        self::assertTrue($token->isVisible());
        self::assertSame(1, $tokens->saveCount);
    }

    public function test_unselected_boss_remains_hidden_during_exploration(): void
    {
        $token = $this->bossToken(TableTokenVisibility::HIDDEN);
        $tokens = new LairParticipantTokenStore($token);
        $service = new LairEncounterParticipant(
            new LairParticipantForgeStore($this->plan()),
            $tokens
        );

        $awakened = $service->awakenIfParticipating(
            'table-1',
            'scene-1',
            ['hero-token']
        );

        self::assertNull($awakened);
        self::assertFalse($token->isVisible());
        self::assertSame(0, $tokens->saveCount);
    }

    public function test_stale_forge_metadata_cannot_reveal_an_unrelated_token(): void
    {
        $token = TableToken::create(
            'boss-token', 'table-1', 'scene-1', 'Definitely Not The Hydra',
            TableTokenType::CREATURE, 'gmrt-bestiary:grease-titan', null,
            .5, .5, 1, 1, TableTokenVisibility::HIDDEN,
            new DateTimeImmutable('2026-09-08T12:00:00+01:00')
        );
        $tokens = new LairParticipantTokenStore($token);
        $service = new LairEncounterParticipant(
            new LairParticipantForgeStore($this->plan()),
            $tokens
        );

        self::assertNull($service->awakenIfParticipating(
            'table-1', 'scene-1', ['boss-token']
        ));
        self::assertFalse($token->isVisible());
        self::assertSame(0, $tokens->saveCount);
    }

    public function test_scene_without_forged_boss_has_no_special_encounter_state(): void
    {
        $tokens = new LairParticipantTokenStore(null);
        $service = new LairEncounterParticipant(
            new LairParticipantForgeStore(['scene_type' => 'grand-dungeon']),
            $tokens
        );

        self::assertNull($service->awakenIfParticipating(
            'table-1', 'scene-1', ['hero-token']
        ));
        self::assertSame(0, $tokens->saveCount);
    }

    /** @return array<string,mixed> */
    private function plan(): array
    {
        return [
            'lair_occupant_id' => 'kale-hydra',
            'lair_occupant_token_id' => 'boss-token',
            'lair_occupant_hidden' => true,
        ];
    }

    private function bossToken(string $visibility): TableToken
    {
        return TableToken::create(
            'boss-token', 'table-1', 'scene-1', 'Kale Hydra',
            TableTokenType::CREATURE, 'gmrt-bestiary:kale-hydra', null,
            .5, .5, 1, 1, $visibility,
            new DateTimeImmutable('2026-09-08T12:00:00+01:00')
        );
    }
}

final class LairParticipantForgeStore implements DungeonForgeRepository
{
    /** @param array<string,mixed>|null $plan */
    public function __construct(private ?array $plan) {}
    public function forScene(string $tableId, string $sceneId): ?array { return $this->plan; }
    public function save(string $tableId, string $sceneId, array $plan): void { $this->plan = $plan; }
}

final class LairParticipantTokenStore implements TableTokenRepository
{
    public int $saveCount = 0;
    public function __construct(private ?TableToken $token) {}
    public function forScene(string $tableId, string $sceneId): array { return $this->token ? [$this->token] : []; }
    public function find(string $tableId, string $tokenId): ?TableToken
    {
        return $this->token !== null && $this->token->id() === $tokenId ? $this->token : null;
    }
    public function save(TableToken $token): void { $this->token = $token; ++$this->saveCount; }
    public function delete(string $tableId, string $tokenId): void { $this->token = null; }
}
