<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Satchel;

use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\D20Roller;
use GreatMarketrealmTabletop\Tabletop\Satchel\Services\QuickHandsRoller;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class QuickHandsRollerTest extends TestCase
{
    private function character(): array
    {
        return ['play' => [
            'initiative' => 2,
            'abilities' => ['wisdom' => ['modifier' => 3]],
            'saving_throws' => ['wisdom' => ['modifier' => 5, 'proficient' => true]],
            'skills' => ['perception' => ['modifier' => 5, 'proficient' => true, 'expertise' => false]],
        ]];
    }

    public function testRollsAuthoritativeProjectedSkillModifier(): void
    {
        $roller = new QuickHandsRoller(new class implements D20Roller { public function roll(): int { return 12; } });
        $result = $roller->roll($this->character(), 'skill', 'perception');
        self::assertSame(12, $result['die']);
        self::assertSame(5, $result['modifier']);
        self::assertSame(17, $result['total']);
        self::assertTrue($result['proficient']);
    }

    public function testSupportsAbilitiesSavesAndInitiative(): void
    {
        $roller = new QuickHandsRoller(new class implements D20Roller { public function roll(): int { return 10; } });
        self::assertSame(13, $roller->roll($this->character(), 'ability', 'wisdom')['total']);
        self::assertSame(15, $roller->roll($this->character(), 'save', 'wisdom')['total']);
        self::assertSame(12, $roller->roll($this->character(), 'initiative', 'initiative')['total']);
    }

    public function testRejectsRollsNotPresentInProjection(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new QuickHandsRoller(new class implements D20Roller { public function roll(): int { return 20; } }))->roll($this->character(), 'skill', 'made-up-skill');
    }
}
