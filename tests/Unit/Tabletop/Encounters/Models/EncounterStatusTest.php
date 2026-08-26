<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Encounters\Models;

use GreatMarketrealmTabletop\Tabletop\Encounters\Models\EncounterStatus;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class EncounterStatusTest extends TestCase
{
    public function testEncounterLifecycleVocabularyIsCanonical(): void
    {
        self::assertSame(
            ['preparing', 'active', 'paused', 'ended'],
            EncounterStatus::all()
        );
    }

    public function testUnknownStatusIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        EncounterStatus::assert('awaiting-snacks');
    }
}
