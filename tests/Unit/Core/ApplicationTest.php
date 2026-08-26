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
            '0.4.0-alpha.1',
            Application::instance()->version()
        );
    }
}
