<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Architecture;

use PHPUnit\Framework\TestCase;

final class PluginFoundationTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 3);
    }

    public function testPluginBootstrapHasStableIdentity(): void
    {
        $source = $this->source('great-marketrealm-tabletop.php');

        self::assertStringContainsString(
            'Plugin Name: Great Marketrealm Tabletop',
            $source
        );
        self::assertStringContainsString(
            "define('GMRT_VERSION', '0.22.2-alpha.2')",
            $source
        );
        self::assertStringContainsString(
            'Text Domain: great-marketrealm-tabletop',
            $source
        );
    }

    public function testProductionBootUsesSelfContainedAutoloader(): void
    {
        $source = $this->source('great-marketrealm-tabletop.php');

        self::assertStringContainsString(
            "require_once GMRT_PATH . 'autoload.php';",
            $source
        );
        self::assertStringNotContainsString(
            'vendor/autoload.php',
            $source
        );
    }

    public function testComposerDoesNotEagerlyLoadWordPressGuardedHelpers(): void
    {
        $composer = json_decode(
            $this->source('composer.json'),
            true
        );

        self::assertIsArray($composer);
        self::assertArrayNotHasKey(
            'files',
            $composer['autoload'] ?? []
        );
    }

    public function testCompanionNamespaceIsIsolatedToIntegrationBoundary(): void
    {
        $violations = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $this->root . '/app',
                \FilesystemIterator::SKIP_DOTS
            )
        );

        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $relative = str_replace(
                $this->root . '/',
                '',
                $file->getPathname()
            );

            if (str_starts_with(
                $relative,
                'app/Integration/Companion/'
            )) {
                continue;
            }

            if (str_contains(
                (string) file_get_contents($file->getPathname()),
                'GreatMarketrealmCompanion\\'
            )) {
                $violations[] = $relative;
            }
        }

        self::assertSame([], $violations);
    }

    public function testTableDomainDoesNotReachIntoCompanionInternals(): void
    {
        $tables = $this->root . '/app/Tables';
        $violations = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $tables,
                \FilesystemIterator::SKIP_DOTS
            )
        );

        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            if (str_contains(
                (string) file_get_contents($file->getPathname()),
                'GreatMarketrealmCompanion\\'
            )) {
                $violations[] = $file->getPathname();
            }
        }

        self::assertSame([], $violations);
    }

    public function testOperationalTableRulesRemainInTableDomain(): void
    {
        self::assertFileExists($this->root . '/app/Tables/Services/TableLeaseManager.php');
        self::assertFileExists($this->root . '/app/Tables/Policies/WordPressTableLeasePolicy.php');
        self::assertFileExists($this->root . '/app/Tables/Policies/WordPressTableStewardOverride.php');
    }

    public function testMembershipDomainKeepsCompanionAsOpaqueReference(): void
    {
        $memberships = $this->root
            . '/app/Tables/Memberships';
        $violations = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $memberships,
                \FilesystemIterator::SKIP_DOTS
            )
        );

        foreach ($iterator as $file) {
            if (
                ! $file->isFile()
                || $file->getExtension() !== 'php'
            ) {
                continue;
            }

            if (str_contains(
                (string) file_get_contents(
                    $file->getPathname()
                ),
                'GreatMarketrealmCompanion\\'
            )) {
                $violations[] = $file->getPathname();
            }
        }

        self::assertSame([], $violations);
    }

    public function testInitialRoadmapNamesTheEmptyTable(): void
    {
        self::assertStringContainsString(
            'Phase IV.1 — The Empty Table',
            $this->source('ROADMAP.md')
        );
    }

    private function source(string $relative): string
    {
        $source = file_get_contents($this->root . '/' . $relative);
        self::assertIsString($source);

        return $source;
    }

    public function testBattlemapDomainDoesNotDependOnCompanionInternals(): void
    {
        $path = $this->root . '/app/Tables/Scenes';
        $violations = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                if (str_contains(
                    (string) file_get_contents($file->getPathname()),
                    'GreatMarketrealmCompanion\\'
                )) {
                    $violations[] = $file->getPathname();
                }
            }
        }

        self::assertSame([], $violations);
    }


    public function testTokenDomainKeepsCompanionSourcesOpaque(): void
    {
        $path = $this->root
            . '/app/Tables/Tokens';
        $violations = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $path,
                \FilesystemIterator::SKIP_DOTS
            )
        );

        foreach ($iterator as $file) {
            if (
                $file->isFile()
                && $file->getExtension() === 'php'
                && str_contains(
                    (string) file_get_contents(
                        $file->getPathname()
                    ),
                    'GreatMarketrealmCompanion\\'
                )
            ) {
                $violations[] =
                    $file->getPathname();
            }
        }

        self::assertSame([], $violations);
    }

    public function testTabletopRouteIsReservedForVisibleShell(): void
    {
        $roadmap = $this->source(
            'ROADMAP.md'
        );

        self::assertStringContainsString(
            '/tabletop/',
            $roadmap
        );
        self::assertStringContainsString(
            'Phase IV.7 — The Tabletop Chamber',
            $roadmap
        );
    }


    public function testTabletopPresentationDoesNotReachIntoCompanionInternals(): void
    {
        $path = $this->root
            . '/app/Tabletop';
        $violations = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $path,
                \FilesystemIterator::SKIP_DOTS
            )
        );

        foreach ($iterator as $file) {
            if (
                $file->isFile()
                && $file->getExtension() === 'php'
                && str_contains(
                    (string) file_get_contents(
                        $file->getPathname()
                    ),
                    'GreatMarketrealmCompanion\\'
                )
            ) {
                $violations[] =
                    $file->getPathname();
            }
        }

        self::assertSame([], $violations);
    }

    public function testTabletopPageHostDoesNotOwnWordPressRewritePath(): void
    {
        $provider = $this->source(
            'app/Tabletop/TabletopServiceProvider.php'
        );

        self::assertStringContainsString(
            'TabletopShortcode',
            $provider
        );
        self::assertStringNotContainsString(
            'TabletopRoute',
            $provider
        );
        self::assertStringNotContainsString(
            'template_redirect',
            $provider
        );
    }


    public function testLivingTableRulesStayServerAuthoritative(): void
    {
        $movement = $this->source(
            'app/Tabletop/Movement/Services/TabletopMovement.php'
        );
        $client = $this->source(
            'assets/js/tabletop.js'
        );

        self::assertStringContainsString(
            'TabletopMovementPolicy',
            $movement
        );
        self::assertStringContainsString(
            'expectedRevision',
            $movement
        );
        self::assertStringContainsString(
            'gmrt_move_token',
            $client
        );
        self::assertStringNotContainsString(
            'update_option(',
            $client
        );
    }


    public function testEncounterEngineReferencesTokensRatherThanCompanionCharacters(): void
    {
        $source = $this->source(
            'app/Tabletop/Encounters/Models/EncounterCombatant.php'
        );

        self::assertStringContainsString(
            'tokenId',
            $source
        );
        self::assertStringNotContainsString(
            'GreatMarketrealmCompanion\\',
            $source
        );
        self::assertStringNotContainsString(
            'characterId',
            $source
        );
    }

    public function testEncounterInitiativeOrderingIsServerDeterministic(): void
    {
        $source = $this->source(
            'app/Tabletop/Encounters/Models/Encounter.php'
        );

        self::assertStringContainsString(
            'initiativeModifier()',
            $source
        );
        self::assertStringContainsString(
            'strcmp(',
            $source
        );
        self::assertStringContainsString(
            'tokenId()',
            $source
        );
    }

}
