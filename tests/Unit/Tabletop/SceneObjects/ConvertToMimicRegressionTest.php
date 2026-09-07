<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\SceneObjects;
use PHPUnit\Framework\TestCase;
final class ConvertToMimicRegressionTest extends TestCase
{
    private function source(string $path): string { return (string) file_get_contents(dirname(__DIR__,4).'/'.$path); }
    public function test_dialog_keeps_pippins_warning(): void {
        $v=$this->source('app/Tabletop/Views/chamber.php');
        self::assertStringContainsString('⚠ CONVERT TO MIMIC',$v); self::assertStringContainsString('This object will continue to look completely innocent.',$v);
        self::assertStringContainsString('Pippin’s advice:',$v); self::assertStringContainsString('“Don’t.”',$v);
    }
    public function test_dropdown_filters_projected_bestiary_to_mimics(): void {
        $v=$this->source('app/Tabletop/Views/chamber.php');
        self::assertStringContainsString('MimicBestiaryFilter::arrayIsMimic($creature)',$v); self::assertStringContainsString('Choose a Mimic from the Bestiary',$v);
    }
    public function test_filter_recognises_name_kind_or_trait_without_second_catalogue(): void {
        $f=$this->source('app/Tabletop/Bestiary/Services/MimicBestiaryFilter.php');
        self::assertStringContainsString("(string) (\$creature['name'] ?? '')",$f); self::assertStringContainsString("(string) (\$creature['kind'] ?? '')",$f);
        self::assertStringContainsString("(array) (\$creature['traits'] ?? [])",$f); self::assertStringContainsString("stripos(\$value, 'mimic') !== false",$f);
    }
    public function test_server_rejects_non_mimic_bestiary_ids_even_if_posted_manually(): void {
        $v=$this->source('app/Tabletop/Views/chamber.php');
        self::assertStringContainsString('! MimicBestiaryFilter::isMimic($creature)',$v);
    }
    public function test_conversion_deploys_the_selected_bestiary_creature_at_furniture_position(): void {
        $v=$this->source('app/Tabletop/Views/chamber.php');
        self::assertStringContainsString('$bestiaryDeploymentManager->deployAtPoint(',$v); self::assertStringContainsString('$existingObject->x()',$v);
        self::assertStringContainsString('$existingObject->y()',$v); self::assertStringContainsString('$creature->id()',$v);
    }
    public function test_furniture_is_removed_only_after_successful_creature_deployment(): void {
        $v=$this->source('app/Tabletop/Views/chamber.php');
        $deploy=strpos($v,'$bestiaryDeploymentManager->deployAtPoint('); $remove=strpos($v,'$sceneObjectRepository->remove(',$deploy);
        self::assertNotFalse($deploy); self::assertNotFalse($remove); self::assertGreaterThan($deploy,$remove);
    }
    public function test_only_mimic_capable_furniture_can_convert(): void {
        $v=$this->source('app/Tabletop/Views/chamber.php'); $j=$this->source('assets/js/tabletop.js');
        self::assertStringContainsString("empty(\$properties['mimic_capable'])",$v); self::assertStringContainsString("object.dataset.mimicCapable !== 'true'",$j);
    }
    public function test_browser_refreshes_chamber_after_conversion_so_creature_replaces_object(): void {
        $j=$this->source('assets/js/tabletop.js');
        self::assertStringContainsString("submitSceneObjectAction('convert_mimic'",$j); self::assertStringContainsString('await replaceChamber(message, null);',$j);
    }
}
