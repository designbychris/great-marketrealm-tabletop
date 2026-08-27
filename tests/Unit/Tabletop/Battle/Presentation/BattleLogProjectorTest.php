<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Battle\Presentation;

use DateTimeImmutable;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\BattleEvent;
use GreatMarketrealmTabletop\Tabletop\Battle\Presentation\BattleLogProjector;
use PHPUnit\Framework\TestCase;

final class BattleLogProjectorTest extends TestCase
{
    private BattleLogProjector $projector;

    protected function setUp(): void
    {
        $this->projector = new BattleLogProjector();
    }

    public function testAttackAndDamageEventsBecomeOneChronicleEntry(): void
    {
        $entries = $this->projector->project(
            [
                $this->event(
                    'attack-resolved',
                    'a',
                    [
                        'target_token_id' => 'b',
                        'result' => 'hit',
                    ]
                ),
                $this->event(
                    'damage-applied',
                    'a',
                    [
                        'target_token_id' => 'b',
                        'damage_adjustment' => [
                            'resolved_damage' => 4,
                            'damage_type' => 'fire',
                            'effects' => [],
                        ],
                        'vitality' => [
                            'current_hp' => 8,
                            'maximum_hp' => 12,
                        ],
                    ]
                ),
            ],
            ['a' => 'Auby', 'b' => 'Slime']
        );

        self::assertCount(1, $entries);
        self::assertSame(
            'Auby hit Slime — 4 FIRE damage; 8/12 HP.',
            $entries[0]['summary']
        );
    }

    public function testAttackDeedEventIsSuppressedAsDuplicate(): void
    {
        $entries = $this->projector->project(
            [
                $this->event(
                    'deed-performed',
                    'a',
                    ['deed' => 'attack']
                ),
                $this->event(
                    'attack-resolved',
                    'a',
                    [
                        'target_token_id' => 'b',
                        'result' => 'miss',
                    ]
                ),
            ],
            ['a' => 'Auby', 'b' => 'Slime']
        );

        self::assertCount(1, $entries);
        self::assertSame(
            'Auby missed Slime.',
            $entries[0]['summary']
        );
    }

    public function testNonAttackDeedIsChronicled(): void
    {
        $entries = $this->projector->project(
            [
                $this->event(
                    'deed-performed',
                    'a',
                    ['deed' => 'dodge']
                ),
            ],
            ['a' => 'Auby']
        );

        self::assertSame(
            'Auby used Dodge.',
            $entries[0]['summary']
        );
    }

    public function testConditionLifecycleIsChronicled(): void
    {
        $entries = $this->projector->project(
            [
                $this->event(
                    'condition-applied',
                    'a',
                    ['condition' => 'poisoned']
                ),
                $this->event(
                    'condition-expired',
                    'a',
                    ['condition' => 'poisoned']
                ),
            ],
            ['a' => 'Frosty']
        );

        self::assertSame(
            "Frosty's Poisoned expired.",
            $entries[0]['summary']
        );
        self::assertSame(
            'Frosty became Poisoned.',
            $entries[1]['summary']
        );
    }

    public function testHiddenActorEventsAreNotProjected(): void
    {
        $entries = $this->projector->project(
            [
                $this->event(
                    'deed-performed',
                    'hidden',
                    ['deed' => 'dodge']
                ),
            ],
            ['visible' => 'Auby']
        );

        self::assertSame([], $entries);
    }

    public function testChronicleIsNewestFirstAndLimitedToTwelve(): void
    {
        $events = [];

        for ($i = 1; $i <= 15; ++$i) {
            $events[] = new BattleEvent(
                'event-' . $i,
                'table',
                'encounter',
                'deed-performed',
                'a',
                $i,
                0,
                new DateTimeImmutable(),
                ['deed' => 'dodge']
            );
        }

        $entries = $this->projector->project(
            $events,
            ['a' => 'Auby']
        );

        self::assertCount(12, $entries);
        self::assertSame(15, $entries[0]['round']);
        self::assertSame(4, $entries[11]['round']);
    }

    public function testResistanceEffectAppearsInDamageSummary(): void
    {
        $entries = $this->projector->project(
            [
                $this->event(
                    'attack-resolved',
                    'a',
                    [
                        'target_token_id' => 'b',
                        'result' => 'hit',
                    ]
                ),
                $this->event(
                    'damage-applied',
                    'a',
                    [
                        'damage_adjustment' => [
                            'resolved_damage' => 2,
                            'damage_type' => 'slashing',
                            'effects' => ['resistant'],
                        ],
                        'vitality' => [
                            'current_hp' => 6,
                            'maximum_hp' => 18,
                        ],
                    ]
                ),
            ],
            ['a' => 'Auby', 'b' => 'Slime']
        );

        self::assertStringContainsString(
            'SLASHING RESIST',
            $entries[0]['summary']
        );
    }

    private function event(
        string $type,
        string $tokenId,
        array $payload
    ): BattleEvent {
        return new BattleEvent(
            bin2hex(random_bytes(8)),
            'table',
            'encounter',
            $type,
            $tokenId,
            2,
            1,
            new DateTimeImmutable(),
            $payload
        );
    }
}
