<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Conditions\Repositories;
use DateTimeImmutable;
use GreatMarketrealmTabletop\Tabletop\Conditions\Models\TokenCondition;
use GreatMarketrealmTabletop\Tabletop\Conditions\Repositories\WordPressConditionRepository;
use PHPUnit\Framework\TestCase;
final class WordPressConditionRepositoryTest extends TestCase {
 protected function setUp():void {$GLOBALS['gmrt_test_options']=[];}
 public function testConditionPersistsWithoutAutoload():void {
  $r=new WordPressConditionRepository();$r->save('table','token',new TokenCondition('token','poisoned',2,new DateTimeImmutable()));
  self::assertSame('poisoned',$r->forToken('table','token')[0]->condition());
  self::assertFalse($GLOBALS['gmrt_test_options']['gmrt_token_conditions']['autoload']);
 }
 public function testSavingSameConditionIsIdempotent():void {
  $r=new WordPressConditionRepository();$r->save('table','token',new TokenCondition('token','poisoned',2,new DateTimeImmutable()));
  $r->save('table','token',new TokenCondition('token','poisoned',4,new DateTimeImmutable()));
  self::assertCount(1,$r->forToken('table','token'));self::assertSame(4,$r->forToken('table','token')[0]->turnsRemaining());
 }
 public function testConditionMayBeRemoved():void {
  $r=new WordPressConditionRepository();$r->save('table','token',new TokenCondition('token','prone',null,new DateTimeImmutable()));
  $r->remove('table','token','prone');self::assertSame([],$r->forToken('table','token'));
 }
}
