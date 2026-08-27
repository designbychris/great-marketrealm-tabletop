<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tabletop\Testing;
defined('ABSPATH') || exit;
final class TestTableBlueprint
{
    public const TABLE_NAME="Sage's Combat Testing Grounds";
    public const SCENE_NAME='The Training Yard';
    public const ENCOUNTER_NAME='The Extremely Serious Training Exercise';
    public function tokens(int $dm):array
    {
        return [
            ['key'=>'auby','label'=>'Auby','type'=>'character','source'=>'gmrt-test:auby','controller'=>$dm,'x'=>0.25,'y'=>0.50,'hp'=>24,'ac'=>14,'attack'=>5,'range'=>[5,5],'damage'=>[1,8,3,'slashing'],'defenses'=>[[],[],[]],'initiative'=>16,'arsenal'=>[
                ['id'=>'staff','name'=>"Keeper's Staff",'kind'=>'melee-weapon','attack'=>5,'range'=>[5,5],'damage'=>[1,6,3,'bludgeoning'],'properties'=>['versatile']],
                ['id'=>'spark','name'=>"Keeper's Spark",'kind'=>'spell','attack'=>6,'range'=>[60,60],'damage'=>[1,8,0,'radiant'],'properties'=>['spell-attack']],
            ]],
            ['key'=>'slime','label'=>'Training Slime','type'=>'creature','source'=>'gmrt-test:training-slime','controller'=>null,'x'=>0.62,'y'=>0.43,'hp'=>18,'ac'=>11,'attack'=>3,'range'=>[5,5],'damage'=>[1,6,1,'poison'],'defenses'=>[['slashing'],[],[]],'initiative'=>12,'arsenal'=>[
                ['id'=>'slam','name'=>'Slime Slam','kind'=>'natural','attack'=>3,'range'=>[5,5],'damage'=>[1,6,1,'bludgeoning'],'properties'=>[]],
                ['id'=>'toxic-spit','name'=>'Toxic Spit','kind'=>'ranged-weapon','attack'=>3,'range'=>[20,40],'damage'=>[1,4,1,'poison'],'properties'=>['ranged']],
            ]],
            ['key'=>'cheese','label'=>'Frosty Cheese Thing','type'=>'creature','source'=>'gmrt-test:frosty-cheese','controller'=>null,'x'=>0.72,'y'=>0.62,'hp'=>22,'ac'=>12,'attack'=>4,'range'=>[30,60],'damage'=>[1,6,2,'cold'],'defenses'=>[[],['fire'],[]],'initiative'=>10,'arsenal'=>[
                ['id'=>'chill-bite','name'=>'Chill Bite','kind'=>'natural','attack'=>4,'range'=>[5,5],'damage'=>[1,6,2,'cold'],'properties'=>[]],
                ['id'=>'frost-shard','name'=>'Frost Shard','kind'=>'ranged-weapon','attack'=>4,'range'=>[30,60],'damage'=>[1,6,2,'cold'],'properties'=>['ranged']],
            ]],
            ['key'=>'dummy','label'=>'Suspicious Training Dummy','type'=>'creature','source'=>'gmrt-test:suspicious-dummy','controller'=>null,'x'=>0.55,'y'=>0.72,'hp'=>30,'ac'=>13,'attack'=>2,'range'=>[30,60],'damage'=>[1,4,0,'fire'],'defenses'=>[[],[],['poison']],'initiative'=>8,'arsenal'=>[
                ['id'=>'wooden-fist','name'=>'Wooden Fist','kind'=>'improvised','attack'=>2,'range'=>[5,5],'damage'=>[1,4,0,'bludgeoning'],'properties'=>['improvised']],
                ['id'=>'ember-pop','name'=>'Ember Pop','kind'=>'ranged-weapon','attack'=>2,'range'=>[30,60],'damage'=>[1,4,0,'fire'],'properties'=>['ranged']],
            ]],
        ];
    }
}
