<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Core;

use GreatMarketrealmTabletop\Core\Application;
use GreatMarketrealmTabletop\Tests\TestCase;

final class ApplicationTest extends TestCase
{
    public function testApplicationExposesInitialVersion(): void
    {
        $application = Application::instance();

        self::assertSame('0.27.1-alpha.1', $application->version());
    }
}
