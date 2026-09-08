<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Bestiary;

use GreatMarketrealmTabletop\Tabletop\Bestiary\Services\BestiaryCompatibilityNormalizer;
use PHPUnit\Framework\TestCase;

final class BossParticipationRegressionTest extends TestCase
{
    private function root(string $path): string
    {
        return dirname(__DIR__, 4) . '/' . ltrim($path, '/');
    }

    public function test_qualified_kale_hydra_defense_can_cross_the_bestiary_boundary(): void
    {
        $normalizer = new BestiaryCompatibilityNormalizer();

        self::assertSame(
            'slashing',
            $normalizer->damageType('Slashing (unless fire is used)')
        );
    }

    public function test_combat_provisioner_normalises_defenses_before_battle_model_validation(): void
    {
        $source = file_get_contents(
            $this->root('app/Tabletop/Bestiary/Services/BestiaryCombatProvisioner.php')
        );

        self::assertStringContainsString(
            '$this->damageTypes($creature->resistances())',
            $source
        );
        self::assertStringContainsString(
            '$this->compatibility->damageType((string) $type)',
            $source
        );
        self::assertStringContainsString(
            'catch (\\InvalidArgumentException)',
            $source
        );
    }

    public function test_live_tokens_receive_the_same_interaction_binding_as_initial_tokens(): void
    {
        $js = file_get_contents($this->root('assets/js/tabletop.js'));

        self::assertStringContainsString(
            'function bindTokenInteractions(token)',
            $js
        );
        self::assertStringContainsString(
            "token.dataset.tokenInteractionsBound === '1'",
            $js
        );
        self::assertGreaterThanOrEqual(
            2,
            substr_count($js, 'bindTokenInteractions(')
        );
    }

    public function test_live_token_refresh_preserves_hidden_keeper_state_and_source_reference(): void
    {
        $js = file_get_contents($this->root('assets/js/tabletop.js'));

        self::assertStringContainsString(
            "node.dataset.tokenSource = String(token.source_reference || '')",
            $js
        );
        self::assertStringContainsString(
            "'is-hidden-token'",
            $js
        );
        self::assertStringContainsString(
            "String(token.visibility || '') === 'hidden'",
            $js
        );
    }
}
