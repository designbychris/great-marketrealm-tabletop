<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tabletop\Testing;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\CombatProfile;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\DamageProfile;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\DamageDefenseProfile;
use GreatMarketrealmTabletop\Tabletop\Battle\Models\Vitality;
use GreatMarketrealmTabletop\Tabletop\Battle\Repositories\WordPressCombatProfileRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Repositories\WordPressDamageProfileRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Repositories\WordPressDamageDefenseRepository;
use GreatMarketrealmTabletop\Tabletop\Battle\Repositories\WordPressVitalityRepository;
use GreatMarketrealmTabletop\Tabletop\Encounters\Services\EncounterManagerFactory;
use GreatMarketrealmTabletop\Tables\Scenes\Models\GridType;
use GreatMarketrealmTabletop\Tables\Scenes\Services\TableSceneManagerFactory;
use GreatMarketrealmTabletop\Tables\Services\TableRegistryFactory;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableTokenVisibility;
use GreatMarketrealmTabletop\Tables\Tokens\Services\TableTokenManagerFactory;
use GreatMarketrealmTabletop\Tables\Tokens\Repositories\WordPressTableTokenRepository;
use RuntimeException;
defined('ABSPATH') || exit;

final class TestTableProvisioner
{
    private TestTableBlueprint $blueprint;
    public function __construct(?TestTableBlueprint $blueprint=null)
    { $this->blueprint=$blueprint ?? new TestTableBlueprint(); }

    public function prepare(int $userId): string
    {
        if($userId<1){throw new RuntimeException('A signed-in user is required to prepare a test Table.');}
        $registry=TableRegistryFactory::make();
        foreach($registry->all() as $existing){
            if($existing->dungeonMasterUserId()===$userId
                && $existing->name()===TestTableBlueprint::TABLE_NAME
                && $existing->status()!=='ended'){
                $this->syncCombatProfiles(
                    $existing->id(),
                    $userId
                );
                return $existing->id();
            }
        }
        $table=$registry->prepare($userId,TestTableBlueprint::TABLE_NAME);
        $registry->activate($table->id());

        $scenes=TableSceneManagerFactory::make();
        $scene=$scenes->create($table->id(),TestTableBlueprint::SCENE_NAME,$this->battlemapAttachment(),960,640,GridType::SQUARE,64);
        $scenes->activate($table->id(),$scene->id());

        $tokens=TableTokenManagerFactory::make();
        $combat=new WordPressCombatProfileRepository();
        $damage=new WordPressDamageProfileRepository();
        $defenses=new WordPressDamageDefenseRepository();
        $vitality=new WordPressVitalityRepository();
        $placed=[];
        foreach($this->blueprint->tokens($userId) as $f){
            $token=$tokens->place($table->id(),$scene->id(),(string)$f['label'],(string)$f['type'],(string)$f['source'],
                is_int($f['controller'])?$f['controller']:null,(float)$f['x'],(float)$f['y'],1,1,TableTokenVisibility::VISIBLE);
            $placed[(string)$f['key']]=$token;
            $range=$f['range'];
            $combat->save($table->id(),new CombatProfile(
                $token->id(),
                (int)$f['ac'],
                (int)$f['attack'],
                (int)$range[0],
                (int)$range[1]
            ));
            $dice=$f['damage'];
            $damage->save($table->id(),new DamageProfile($token->id(),(int)$dice[0],(int)$dice[1],(int)$dice[2],(string)$dice[3]));
            $defense=$f['defenses'];
            $defenses->save($table->id(),new DamageDefenseProfile($token->id(),$defense[0],$defense[1],$defense[2]));
            $vitality->save($table->id(),new Vitality($token->id(),(int)$f['hp'],(int)$f['hp']));
        }

        $manager=EncounterManagerFactory::make();
        $enc=$manager->prepare($table->id(),$userId,$scene->id(),TestTableBlueprint::ENCOUNTER_NAME);
        foreach($this->blueprint->tokens($userId) as $f){
            $enc=$manager->addCombatant($table->id(),$userId,$enc->id(),$placed[(string)$f['key']]->id(),(int)$f['initiative'],0,$enc->revision());
        }
        $manager->start($table->id(),$userId,$enc->id(),$enc->revision());
        return $table->id();
    }

    private function syncCombatProfiles(
        string $tableId,
        int $userId
    ): void {
        $scenes = TableSceneManagerFactory::make();
        $scene = $scenes->active($tableId);

        if ($scene === null) {
            return;
        }

        $tokens = (new WordPressTableTokenRepository())
            ->forScene(
                $tableId,
                $scene->id()
            );

        $bySource = [];
        foreach ($tokens as $token) {
            $bySource[$token->sourceReference()]
                = $token;
        }

        $combat = new WordPressCombatProfileRepository();
        $damage = new WordPressDamageProfileRepository();
        $defenses = new WordPressDamageDefenseRepository();

        foreach ($this->blueprint->tokens($userId) as $fixture) {
            $token = $bySource[
                (string) $fixture['source']
            ] ?? null;

            if ($token === null) {
                continue;
            }

            $range = $fixture['range'];
            $combat->save(
                $tableId,
                new CombatProfile(
                    $token->id(),
                    (int) $fixture['ac'],
                    (int) $fixture['attack'],
                    (int) $range[0],
                    (int) $range[1]
                )
            );

            $dice = $fixture['damage'];
            $damage->save(
                $tableId,
                new DamageProfile(
                    $token->id(),
                    (int) $dice[0],
                    (int) $dice[1],
                    (int) $dice[2],
                    (string) $dice[3]
                )
            );

            $defense = $fixture['defenses'];
            $defenses->save(
                $tableId,
                new DamageDefenseProfile(
                    $token->id(),
                    $defense[0],
                    $defense[1],
                    $defense[2]
                )
            );
        }
    }

    private function battlemapAttachment(): int
    {
        $existing=(int)get_option('gmrt_test_battlemap_attachment_id',0);
        if($existing>0 && get_post($existing)!==null){return $existing;}
        $source=GMRT_PATH.'assets/images/test-table-battlemap.png';
        if(!is_file($source)){throw new RuntimeException('The bundled test battlemap is missing.');}
        $upload=wp_upload_bits('gmrt-test-table-battlemap.png',null,(string)file_get_contents($source));
        if(!empty($upload['error'])){throw new RuntimeException('The test battlemap could not be copied to WordPress Media.');}
        $id=wp_insert_attachment(['post_mime_type'=>'image/png','post_title'=>'GMRT Test Table Battlemap','post_status'=>'inherit'],(string)$upload['file']);
        if(is_wp_error($id)||(int)$id<1){throw new RuntimeException('The test battlemap attachment could not be created.');}
        update_option('gmrt_test_battlemap_attachment_id',(int)$id,false);
        return (int)$id;
    }
}
