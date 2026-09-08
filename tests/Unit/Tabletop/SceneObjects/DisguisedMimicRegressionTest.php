<?php
declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\SceneObjects;

use PHPUnit\Framework\TestCase;

final class DisguisedMimicRegressionTest extends TestCase
{
    private function source(string $path): string
    {
        return (string) file_get_contents(dirname(__DIR__, 4) . '/' . $path);
    }

    public function test_keeper_can_choose_between_immediate_conversion_and_arming_a_disguise(): void
    {
        $view = $this->source('app/Tabletop/Views/chamber.php');
        self::assertStringContainsString('data-mimic-confirm>Convert Now', $view);
        self::assertStringContainsString('data-mimic-arm>Arm Disguise', $view);
    }

    public function test_armed_mimic_remains_a_scene_object_with_persistent_bestiary_identity(): void
    {
        $view = $this->source('app/Tabletop/Views/chamber.php');
        self::assertStringContainsString("\$objectState['mimic_disguise'] = [", $view);
        self::assertStringContainsString("'creature_id' => \$creature->id()", $view);
        self::assertStringContainsString("'armed' => true", $view);
    }

    public function test_only_real_mimics_can_be_armed(): void
    {
        $view = $this->source('app/Tabletop/Views/chamber.php');
        self::assertStringContainsString('! MimicBestiaryFilter::isMimic($creature)', $view);
        self::assertStringContainsString("empty(\$properties['mimic_capable'])", $view);
    }

    public function test_keeper_only_dom_receives_secret_mimic_identity(): void
    {
        $view = $this->source('app/Tabletop/Views/chamber.php');
        self::assertStringContainsString('<?php if ($state->isDungeonMaster()) : ?>', $view);
        self::assertStringContainsString('data-mimic-armed=', $view);
        self::assertStringContainsString('data-mimic-name=', $view);
    }

    public function test_reveal_deploys_the_stored_mimic_at_exact_furniture_position(): void
    {
        $view = $this->source('app/Tabletop/Views/chamber.php');
        self::assertStringContainsString("\$sceneObjectAction === 'reveal_mimic'", $view);
        self::assertStringContainsString('$bestiaryDeploymentManager->deployAtPoint(', $view);
        self::assertStringContainsString('$existingObject->x()', $view);
        self::assertStringContainsString('$existingObject->y()', $view);
    }

    public function test_reveal_removes_the_furniture_only_after_successful_deployment(): void
    {
        $view = $this->source('app/Tabletop/Views/chamber.php');
        $start = strpos($view, "\$sceneObjectAction === 'reveal_mimic'");
        $deploy = strpos($view, '$bestiaryDeploymentManager->deployAtPoint(', $start);
        $remove = strpos($view, '$sceneObjectRepository->remove(', $deploy);
        self::assertNotFalse($deploy);
        self::assertNotFalse($remove);
        self::assertGreaterThan($deploy, $remove);
    }

    public function test_keeper_can_disarm_without_deleting_the_furnishing(): void
    {
        $view = $this->source('app/Tabletop/Views/chamber.php');
        self::assertStringContainsString("\$sceneObjectAction === 'disarm_mimic'", $view);
        self::assertStringContainsString("unset(\$objectState['mimic_disguise']);", $view);
        self::assertStringContainsString('The disguise has been disarmed.', $view);
    }

    public function test_browser_exposes_arm_reveal_and_disarm_controls(): void
    {
        $js = $this->source('assets/js/tabletop.js');
        self::assertStringContainsString("submitSceneObjectAction('arm_mimic'", $js);
        self::assertStringContainsString("submitSceneObjectAction('reveal_mimic'", $js);
        self::assertStringContainsString("submitSceneObjectAction('disarm_mimic'", $js);
        self::assertStringContainsString('await replaceChamber(message, null);', $js);
    }
}
