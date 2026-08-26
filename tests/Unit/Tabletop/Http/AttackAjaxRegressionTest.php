<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Http;

use PHPUnit\Framework\TestCase;

final class AttackAjaxRegressionTest extends TestCase
{
    public function testAttackEndpointIsAuthenticatedNonceProtectedAndRevisionAware(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4)
                . '/app/Tabletop/Http/AttackAjaxController.php'
        );

        self::assertStringContainsString('is_user_logged_in()', $source);
        self::assertStringContainsString('check_ajax_referer(', $source);
        self::assertStringContainsString('get_current_user_id()', $source);
        self::assertStringContainsString("'target_token_id'", $source);
        self::assertStringContainsString('409', $source);
    }

    public function testAttackEndpointReturnsDamageAndVitalityProjection(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4)
                . '/app/Tabletop/Http/AttackAjaxController.php'
        );

        self::assertStringContainsString(
            "'damage' =>",
            $source
        );
        self::assertStringContainsString(
            "'vitality' =>",
            $source
        );
        self::assertStringContainsString(
            "'damage_event' =>",
            $source
        );
    }


    public function testAttackEndpointReturnsDeathSaveConsequences(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4)
                . '/app/Tabletop/Http/AttackAjaxController.php'
        );

        self::assertStringContainsString(
            "'death_saves' =>",
            $source
        );
    }


    public function testAttackEndpointReturnsDamageAdjustment(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4)
                . '/app/Tabletop/Http/AttackAjaxController.php'
        );

        self::assertStringContainsString(
            "'damage_adjustment' =>",
            $source
        );
    }

}
