<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Cartography;

use PHPUnit\Framework\TestCase;

final class EveryDungeonNeedsADoorRegressionTest extends TestCase
{
    private function source(string $path): string
    {
        return (string) file_get_contents(dirname(__DIR__, 4) . '/' . $path);
    }

    public function test_forge_exposes_an_optional_way_in_control(): void
    {
        $view = $this->source('app/Tabletop/Views/chamber.php');
        self::assertStringContainsString('data-dungeon-forge-entry', $view);
        self::assertStringContainsString('<option value="none" selected>None</option>', $view);
        self::assertStringContainsString('<option value="entrance">Main entrance</option>', $view);
        self::assertStringContainsString('<option value="portal">Arrival portal</option>', $view);
    }

    public function test_dungeon_entrance_is_cut_into_an_exterior_boundary_not_painted_as_furniture(): void
    {
        $js = $this->source('assets/js/tabletop.js');
        self::assertStringContainsString('const forgeDungeonEntrance =', $js);
        self::assertStringContainsString("anchor:{type:'entrance'", $js);
        self::assertStringContainsString('doors.push(dungeonEntrance.door)', $js);
        self::assertStringContainsString("dungeonEntrance?.axis==='horizontal'", $js);
        self::assertStringContainsString("dungeonEntrance?.axis==='vertical'", $js);
    }

    public function test_portal_is_an_interior_arrival_anchor_without_requiring_an_exterior_door(): void
    {
        $js = $this->source('assets/js/tabletop.js');
        self::assertStringContainsString('const forgePortalAnchor =', $js);
        self::assertStringContainsString("return {type:'portal'", $js);
        self::assertStringContainsString("requestedEntry === 'portal'", $js);
    }

    public function test_entry_anchor_is_visible_in_the_draft_and_persisted_projection(): void
    {
        $js = $this->source('assets/js/tabletop.js');
        $controller = $this->source('app/Tabletop/Http/DungeonForgeAjaxController.php');
        self::assertStringContainsString('if (plan.entry_anchor)', $js);
        self::assertStringContainsString('gmrt-forge-entry', $js);
        self::assertStringContainsString("'entry_anchor' => \$plan['entry_anchor']", $controller);
        self::assertStringContainsString("'arrival_threshold_id' => \$arrivalThresholdId", $controller);
    }

    public function test_server_bounds_entry_anchor_vocabulary_and_coordinates(): void
    {
        $controller = $this->source('app/Tabletop/Http/DungeonForgeAjaxController.php');
        self::assertStringContainsString("in_array(\$type, ['entrance', 'portal'], true)", $controller);
        self::assertStringContainsString("['north', 'south', 'east', 'west', 'centre']", $controller);
        self::assertStringContainsString("'x' => \$this->clamp", $controller);
        self::assertStringContainsString("'y' => \$this->clamp", $controller);
    }

    public function test_forge_reuses_the_existing_party_arrival_threshold_system(): void
    {
        $controller = $this->source('app/Tabletop/Http/DungeonForgeAjaxController.php');
        $provider = $this->source('app/Tabletop/TabletopServiceProvider.php');
        self::assertStringContainsString('private ThresholdManager $thresholds', $controller);
        self::assertStringContainsString('$this->thresholds->setPartyArrival(', $controller);
        self::assertStringContainsString('ThresholdManagerFactory::make()', $provider);
    }

    public function test_generated_party_arrival_replaces_only_existing_party_arrival_markers(): void
    {
        $manager = $this->source('app/Tabletop/Atlas/Thresholds/Services/ThresholdManager.php');
        self::assertStringContainsString('public function setPartyArrival(', $manager);
        self::assertStringContainsString('$marker->type() === ThresholdType::PARTY', $manager);
        self::assertStringContainsString('$this->thresholds->delete(', $manager);
        self::assertStringContainsString('ThresholdType::PARTY', $manager);
    }

    public function test_arriving_players_continue_to_use_existing_welcome_adventurer_flow(): void
    {
        $manager = $this->source('app/Tabletop/Atlas/Thresholds/Services/ThresholdManager.php');
        self::assertStringContainsString('public function welcomeAdventurer(', $manager);
        self::assertStringContainsString('$this->arrivalPoint(', $manager);
        self::assertStringContainsString('$this->tokenManager->place(', $manager);
    }
}
