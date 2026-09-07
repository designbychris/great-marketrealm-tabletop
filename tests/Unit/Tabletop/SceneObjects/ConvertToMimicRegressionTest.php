<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\SceneObjects;
use PHPUnit\Framework\TestCase;
final class ConvertToMimicRegressionTest extends TestCase
{
    private function source(string $path): string { return (string) file_get_contents(dirname(__DIR__, 4) . '/' . $path); }
    public function test_keeper_editor_exposes_the_promised_warning(): void {
        $v=$this->source('app/Tabletop/Views/chamber.php');
        self::assertStringContainsString('⚠ CONVERT TO MIMIC',$v); self::assertStringContainsString('This object will continue to look completely innocent.',$v);
        self::assertStringContainsString('Pippin’s advice:',$v); self::assertStringContainsString('“Don’t.”',$v);
    }
    public function test_conversion_chooses_a_real_bestiary_definition(): void {
        $v=$this->source('app/Tabletop/Views/chamber.php');
        self::assertStringContainsString('BestiaryRepositoryFactory::make()',$v); self::assertStringContainsString('$bestiaryRepository->find($creatureId)',$v);
        self::assertStringContainsString('foreach ($bestiary as $creature)',$v);
    }
    public function test_only_mimic_capable_objects_can_convert(): void {
        $v=$this->source('app/Tabletop/Views/chamber.php'); $j=$this->source('assets/js/tabletop.js');
        self::assertStringContainsString("empty(\$properties['mimic_capable'])",$v); self::assertStringContainsString("object.dataset.mimicCapable !== 'true'",$j);
    }
    public function test_conversion_is_persistent_unrevealed_object_state(): void {
        $v=$this->source('app/Tabletop/Views/chamber.php');
        self::assertStringContainsString("\$objectState['mimic'] = [",$v); self::assertStringContainsString("'creature_id' => \$creature->id()",$v);
        self::assertStringContainsString("'revealed' => false",$v);
    }
    public function test_original_scene_object_identity_and_geometry_are_retained(): void {
        $v=$this->source('app/Tabletop/Views/chamber.php');
        self::assertStringContainsString('$existingObject->kind()',$v); self::assertStringContainsString('$existingObject->x()',$v);
        self::assertStringContainsString('$existingObject->rotation()',$v); self::assertStringContainsString('$existingObject->scale()',$v);
    }
    public function test_mimic_name_is_keeper_only_in_rendered_dom(): void {
        $v=$this->source('app/Tabletop/Views/chamber.php');
        self::assertStringContainsString("\$state->isDungeonMaster() ? \$objectMimicName : ''",$v); self::assertStringContainsString('data-mimic-armed=',$v);
    }
    public function test_conversion_can_be_removed_without_deleting_furniture(): void {
        $v=$this->source('app/Tabletop/Views/chamber.php');
        self::assertStringContainsString("\$sceneObjectAction === 'remove_mimic'",$v); self::assertStringContainsString("unset(\$objectState['mimic']);",$v);
    }
    public function test_browser_uses_existing_scene_object_authoring_boundary(): void {
        $j=$this->source('assets/js/tabletop.js');
        self::assertStringContainsString("submitSceneObjectAction('convert_mimic'",$j); self::assertStringContainsString("submitSceneObjectAction('remove_mimic'",$j);
        self::assertStringContainsString("armed ? 'Remove Mimic Conversion' : '⚠ Convert to Mimic'",$j);
    }
}
