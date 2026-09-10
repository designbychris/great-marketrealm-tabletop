<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Integration\Expansions;

use GreatMarketrealmTabletop\Tabletop\Bestiary\Models\BestiaryCreature;
use GreatMarketrealmTabletop\Tabletop\Bestiary\Services\ExternalBestiaryMapper;
use PHPUnit\Framework\TestCase;

final class TabletopOpensSameBookRegressionTest extends TestCase
{
    private function root(string $path): string
    {
        return dirname(__DIR__, 4) . '/' . ltrim($path, '/');
    }

    public function test_expansion_bestiary_source_requires_linked_campaign_scope(): void
    {
        $source = (string) file_get_contents(
            $this->root('app/Integration/Expansions/ExpansionBestiarySource.php')
        );

        self::assertStringContainsString('linkedCampaign(', $source);
        self::assertStringContainsString("['expansion_keys']", $source);
        self::assertStringContainsString('fromExpansionAndType(', $source);
        self::assertStringContainsString("'monster'", $source);
    }

    public function test_expansion_source_uses_active_content_api_without_copying_definitions(): void
    {
        $source = (string) file_get_contents(
            $this->root('app/Integration/Expansions/ExpansionBestiarySource.php')
        );

        self::assertStringContainsString('GreatMarketrealmExpansions\\\\active_content', $source);
        self::assertStringContainsString("MINIMUM_ACTIVE_CONTENT_API_VERSION = '1.0.0'", $source);
        self::assertStringNotContainsString('update_option(', $source);
        self::assertStringNotContainsString('update_post_meta(', $source);
    }

    public function test_gmrexp_nested_monster_measures_are_projected_for_tabletop(): void
    {
        $source = (string) file_get_contents(
            $this->root('app/Integration/Expansions/ExpansionBestiarySource.php')
        );

        self::assertStringContainsString("\$data['armour_class']", $source);
        self::assertStringContainsString("\$armour['value']", $source);
        self::assertStringContainsString("\$data['hit_points']", $source);
        self::assertStringContainsString("\$hitPoints['average']", $source);
        self::assertStringContainsString("\$speed['walk']", $source);
    }

    public function test_free_form_actions_are_not_guessed_into_live_attacks(): void
    {
        $source = (string) file_get_contents(
            $this->root('app/Integration/Expansions/ExpansionBestiarySource.php')
        );

        self::assertStringContainsString('Only mechanically structured action rules cross', $source);
        self::assertStringContainsString("(\$rule['kind'] ?? '')", $source);
        self::assertStringContainsString("!== 'attack'", $source);
        self::assertStringContainsString("'reference_actions'", $source);
    }

    public function test_menagerie_factory_adds_expansion_shelf_only_with_table_context(): void
    {
        $factory = (string) file_get_contents(
            $this->root('app/Tabletop/Bestiary/Services/BestiaryRepositoryFactory.php')
        );

        self::assertStringContainsString('ExpansionBestiarySource', $factory);
        self::assertStringContainsString("\$tableId !== '' && \$viewerUserId > 0", $factory);
        self::assertStringContainsString('new CompanionBestiarySource()', $factory);
    }

    public function test_deployment_revalidates_campaign_scoped_bestiary_at_action_time(): void
    {
        $manager = (string) file_get_contents(
            $this->root('app/Tabletop/Bestiary/Services/BestiaryDeploymentManager.php')
        );

        self::assertStringContainsString(
            'BestiaryRepositoryFactory::make($tableId, $viewerUserId)',
            $manager
        );
        self::assertStringContainsString("for this Campaign", $manager);
    }

    public function test_expansion_provenance_survives_external_mapper(): void
    {
        $mapper = new ExternalBestiaryMapper();
        $creature = $mapper->map([
            'id' => 'midnight-menu:monster:pizza-rat',
            'name' => 'Pizza Rat',
            'kind' => 'Monstrosity',
            'size' => 'Tiny',
            'armor_class' => 13,
            'hit_points' => 5,
            'speed_feet' => 30,
            'source' => 'gmrexp:midnight-menu:monster:pizza-rat',
            'canonical_id' => 'midnight-menu:monster:pizza-rat',
            'expansion_key' => 'midnight-menu',
            'expansion_label' => 'Midnight Menu',
        ]);

        self::assertInstanceOf(BestiaryCreature::class, $creature);
        $record = $creature->toArray();
        self::assertSame('midnight-menu', $record['expansion_key']);
        self::assertSame('Midnight Menu', $record['expansion_label']);
        self::assertSame('midnight-menu:monster:pizza-rat', $record['canonical_id']);
    }

    public function test_keeper_bestiary_marks_expansion_cards_with_accessible_sourcebook_label(): void
    {
        $view = (string) file_get_contents(
            $this->root('app/Tabletop/Views/chamber.php')
        );
        $css = (string) file_get_contents(
            $this->root('assets/css/tabletop.css')
        );

        self::assertStringContainsString('gmrt-bestiary-card--expansion', $view);
        self::assertStringContainsString('ALMANAC ·', $view);
        self::assertStringContainsString('Sourcebook Actions', $view);
        self::assertStringContainsString('data-expansion=', $view);
        self::assertStringContainsString('.gmrt-bestiary-card--expansion-midnight-menu', $css);
        self::assertStringContainsString('@media (forced-colors: active)', $css);
    }
}
