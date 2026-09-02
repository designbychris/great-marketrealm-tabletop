<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Chronicle;

use PHPUnit\Framework\TestCase;

final class DeedsRememberEveryAdventurerRegressionTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function testEverySatchelRollCarriesTheVisibleEncounterHint(): void
    {
        $javascript = (string) file_get_contents($this->root . '/assets/js/tabletop.js');

        self::assertStringContainsString('const visibleEncounterId = () => String(', $javascript);
        self::assertSame(3, substr_count($javascript, 'encounter_id: visibleEncounterId()'));
    }

    public function testSatchelEndpointsPassTheEncounterHintIntoTheChronicleRecorder(): void
    {
        foreach (['QuickHandsAjaxController.php', 'WeaponHandsAjaxController.php', 'SpellPouchAjaxController.php'] as $file) {
            $source = (string) file_get_contents($this->root . '/app/Tabletop/Http/' . $file);
            self::assertStringContainsString("\$_POST['encounter_id']", $source);
            self::assertStringContainsString('recordSatchelRoll(', $source);
        }
    }

    public function testEncounterHintIsValidatedBeforeBattleChronicleRouting(): void
    {
        $source = (string) file_get_contents($this->root . '/app/Tabletop/Chronicle/Services/TableChronicleRecorder.php');

        self::assertStringContainsString('$this->encounters->find($tableId, $encounterIdHint)', $source);
        self::assertStringContainsString('! $hintedEncounter->isEnded()', $source);
        self::assertStringContainsString('$hintedEncounter->sceneId() === $activeScene->id()', $source);
        self::assertStringContainsString('$encounter = $hintedEncounter;', $source);
    }

    public function testChronicleStillSeparatesBattleAndExplorationLedgers(): void
    {
        $source = (string) file_get_contents($this->root . '/app/Tabletop/Chronicle/Services/TableChronicleRecorder.php');

        self::assertStringContainsString('$this->battleEvents->append(', $source);
        self::assertStringContainsString('$this->chamberEvents->append(', $source);
        self::assertStringContainsString("'satchel-roll'", $source);
    }

    public function testSatchelUsesTheSharedPixelScrollbarLanguage(): void
    {
        $css = (string) file_get_contents($this->root . '/assets/css/tabletop.css');

        self::assertStringContainsString('.gmrt-satchel__panel::-webkit-scrollbar', $css);
        self::assertStringContainsString('scrollbar-color: var(--gmrt-pixel-gold-low) #17120d;', $css);
        self::assertStringContainsString('.gmrt-satchel__panel::-webkit-scrollbar-thumb:hover', $css);
    }
}
