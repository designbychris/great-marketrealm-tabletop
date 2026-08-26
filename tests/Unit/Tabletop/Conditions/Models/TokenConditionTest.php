<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Conditions\Models;
use DateTimeImmutable;
use GreatMarketrealmTabletop\Tabletop\Conditions\Models\TokenCondition;
use PHPUnit\Framework\TestCase;
final class TokenConditionTest extends TestCase {
 public function testPermanentConditionSurvivesTurn():void {
  $c=new TokenCondition('t','poisoned',null,new DateTimeImmutable('2026-08-26T10:00:00+01:00'));
  self::assertSame($c,$c->afterTurn());
 }
 public function testTimedConditionCountsDown():void {
  $c=new TokenCondition('t','stunned',2,new DateTimeImmutable());
  self::assertSame(1,$c->afterTurn()?->turnsRemaining());
 }
 public function testOneTurnConditionExpires():void {
  $c=new TokenCondition('t','stunned',1,new DateTimeImmutable());
  self::assertNull($c->afterTurn());
 }
 public function testConditionRoundTrips():void {
  $c=new TokenCondition('t','prone',3,new DateTimeImmutable('2026-08-26T10:00:00+01:00'));
  self::assertSame($c->toArray(),TokenCondition::reconstitute($c->toArray())->toArray());
 }
}
