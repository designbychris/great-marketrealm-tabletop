<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presentation;

use PHPUnit\Framework\TestCase;

final class FallenCombatantViewRegressionTest extends TestCase
{
    public function testTokensExposeSemanticCombatantState(): void
    {
        $view = (string) file_get_contents(
            dirname(__DIR__, 4)
                . '/app/Tabletop/Views/chamber.php'
        );

        self::assertStringContainsString(
            'data-combatant-state=',
            $view
        );
        self::assertStringContainsString(
            'data-token-state-badge',
            $view
        );
        self::assertStringContainsString(
            'is-state-<?php echo esc_attr(',
            $view
        );
    }

    public function testZeroHpPresentationDoesNotEquateDownWithDeath(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4)
                . '/app/Tabletop/Presentation/CombatantStateProjector.php'
        );

        self::assertStringContainsString(
            "self::DOWNED",
            $source
        );
        self::assertStringContainsString(
            "self::DEFEATED",
            $source
        );
        self::assertStringContainsString(
            "self::DECEASED",
            $source
        );
    }

    public function testClientConsumesServerProjectedCombatantState(): void
    {
        $script = (string) file_get_contents(
            dirname(__DIR__, 4)
                . '/assets/js/tabletop.js'
        );

        self::assertStringContainsString(
            'state.combatant_states || {}',
            $script
        );
        self::assertStringContainsString(
            'updateCombatantState(',
            $script
        );
    }

    public function testDeceasedTokenHasDistinctFinalVisualHook(): void
    {
        $css = (string) file_get_contents(
            dirname(__DIR__, 4)
                . '/assets/css/tabletop.css'
        );

        self::assertStringContainsString(
            '.gmrt-token.is-state-deceased',
            $css
        );
        self::assertStringContainsString(
            '.gmrt-token.is-state-defeated',
            $css
        );
        self::assertStringContainsString(
            '.gmrt-token.is-state-downed',
            $css
        );
    }
}
