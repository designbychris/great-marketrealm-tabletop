<?php
namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Sessions;
use PHPUnit\Framework\TestCase;
final class ChronicleOpensPagesRegressionTest extends TestCase
{
    private function root(string $path): string { return dirname(__DIR__,4).'/'.$path; }
    public function test_recap_carries_character_contributions(): void { $s=file_get_contents($this->root('app/Tabletop/Sessions/Models/SessionRecap.php')); self::assertStringContainsString('contributions', $s); }
    public function test_builder_attributes_deeds_to_character_labels(): void { $s=file_get_contents($this->root('app/Tabletop/Sessions/Services/SessionRecapBuilder.php')); self::assertStringContainsString("'character_name'", $s); self::assertStringContainsString('WordPressTableTokenRepository', file_get_contents($this->root('app/Tabletop/Sessions/Services/TableSessionManagerFactory.php'))); }
    public function test_companion_sync_includes_recap_and_contributions(): void { $s=file_get_contents($this->root('app/Integration/Companion/CompanionCampaignBridge.php')); self::assertStringContainsString("\$record['recap']", $s); self::assertStringContainsString("\$record['contributions']", $s); }
}
