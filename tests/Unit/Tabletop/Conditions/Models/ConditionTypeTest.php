<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Conditions\Models;
use GreatMarketrealmTabletop\Tabletop\Conditions\Models\ConditionType;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
final class ConditionTypeTest extends TestCase {
 public function testCanonicalConditionsAreStable():void {
  self::assertSame(['blinded','charmed','frightened','grappled','poisoned','prone','restrained','stunned'],ConditionType::all());
 }
 public function testUnknownConditionIsRejected():void {
  $this->expectException(InvalidArgumentException::class); ConditionType::assert('mildly-bothered');
 }
}
