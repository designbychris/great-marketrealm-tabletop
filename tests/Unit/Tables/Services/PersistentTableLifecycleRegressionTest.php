<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tables\Services;

use PHPUnit\Framework\TestCase;

final class PersistentTableLifecycleRegressionTest extends TestCase
{
    private function root(string $path): string
    {
        return dirname(__DIR__, 4) . '/' . ltrim($path, '/');
    }

    public function test_persistent_campaign_tables_can_wake_after_a_legacy_lease_end(): void
    {
        $table = file_get_contents($this->root('app/Tables/Models/Table.php'));
        $registry = file_get_contents($this->root('app/Tables/Services/TableRegistry.php'));

        self::assertStringContainsString('public function resume(', $table);
        self::assertStringContainsString('$this->status = TableStatus::ACTIVE;', $table);
        self::assertStringContainsString('$this->endedAt = null;', $table);
        self::assertStringContainsString('public function keepAlive(string $id): Table', $registry);
        self::assertStringContainsString('use GreatMarketrealmTabletop\\Tables\\Models\\TableStatus;', $registry);
        self::assertStringContainsString('$table->resume($now, $this->leases->leaseExpiryFrom($now));', $registry);
    }

    public function test_chamber_entry_and_live_state_keep_the_persistent_table_writable(): void
    {
        $shortcode = file_get_contents($this->root('app/Tabletop/Presentation/TabletopShortcode.php'));
        $ajax = file_get_contents($this->root('app/Tabletop/Http/TabletopAjaxController.php'));
        $provider = file_get_contents($this->root('app/Tabletop/TabletopServiceProvider.php'));

        self::assertStringContainsString('TableRegistryFactory::make()->keepAlive($tableId);', $shortcode);
        self::assertGreaterThanOrEqual(3, substr_count($ajax, '$this->tables?->keepAlive($this->tableId());'));
        self::assertStringContainsString('TableRegistryFactory::make()', $provider);
    }

    public function test_live_polling_renews_only_near_expiry_instead_of_writing_every_five_seconds(): void
    {
        $registry = file_get_contents($this->root('app/Tables/Services/TableRegistry.php'));

        self::assertStringContainsString("new DateInterval('PT300S')", $registry);
        self::assertStringContainsString('$expiresAt === null || $expiresAt <= $renewBy', $registry);
        self::assertStringContainsString('$table->heartbeat($now, $this->leases->leaseExpiryFrom($now));', $registry);
    }
}
