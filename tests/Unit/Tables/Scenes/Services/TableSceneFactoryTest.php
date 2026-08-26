<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tables\Scenes\Services;

use GreatMarketrealmTabletop\Tables\Scenes\Services\TableSceneManager;
use GreatMarketrealmTabletop\Tables\Scenes\Services\TableSceneManagerFactory;
use PHPUnit\Framework\TestCase;

final class TableSceneFactoryTest extends TestCase
{
    public function testProductionFactoryBuildsSceneManager(): void
    {
        self::assertInstanceOf(
            TableSceneManager::class,
            TableSceneManagerFactory::make()
        );
    }
}
