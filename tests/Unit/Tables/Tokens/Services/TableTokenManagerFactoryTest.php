<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tables\Tokens\Services;

use GreatMarketrealmTabletop\Tables\Tokens\Services\TableTokenManager;
use GreatMarketrealmTabletop\Tables\Tokens\Services\TableTokenManagerFactory;
use PHPUnit\Framework\TestCase;

final class TableTokenManagerFactoryTest extends TestCase
{
    public function testProductionFactoryBuildsTokenManager(): void
    {
        self::assertInstanceOf(
            TableTokenManager::class,
            TableTokenManagerFactory::make()
        );
    }
}
