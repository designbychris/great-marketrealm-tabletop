<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Core;

use GreatMarketrealmTabletop\Core\Application;
use PHPUnit\Framework\TestCase;

final class ApplicationTest extends TestCase
{
    public function testApplicationIsASingleton(): void
    {
        self::assertSame(
            Application::instance(),
            Application::instance()
        );
    }

    public function testApplicationBootsOnlyOnce(): void
    {
        $application = Application::instance();

        $application->boot();
        $application->boot();

        self::assertTrue($application->booted());
    }

    public function testApplicationExposesInitialVersion(): void
    {
        self::assertSame(
            '0.14.1-alpha.2',
            Application::instance()->version()
        );
    }

    public function testApplicationExposesBattlemapScenes(): void
    {
        self::assertInstanceOf(
            \GreatMarketrealmTabletop\Tables\Scenes\Services\TableSceneManager::class,
            \GreatMarketrealmTabletop\Core\Application::instance()->scenes()
        );
    }


    public function testApplicationExposesTableTokens(): void
    {
        self::assertInstanceOf(
            \GreatMarketrealmTabletop\Tables\Tokens\Services\TableTokenManager::class,
            \GreatMarketrealmTabletop\Core\Application::instance()->tokens()
        );
    }


    public function testApplicationRegistersTabletopChamberProvider(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 3)
                . '/app/Core/Application.php'
        );

        self::assertStringContainsString(
            'new TabletopServiceProvider()',
            $source
        );
        self::assertStringContainsString(
            '$this->tabletop->register();',
            $source
        );
    }

}
