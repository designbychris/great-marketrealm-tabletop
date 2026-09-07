<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\SceneObjects;

use GreatMarketrealmTabletop\Tabletop\SceneObjects\Models\SceneObjectCategory;
use GreatMarketrealmTabletop\Tabletop\SceneObjects\Repositories\WordPressCustomFurnitureRepository;

defined('ABSPATH') || exit;

/**
 * Keeper-facing catalogue of furnishings that can be placed as Scene Objects.
 *
 * Every furnishing is deliberately marked mimic-capable. The Bestiary-backed
 * conversion workflow belongs to a later interaction phase, but no piece of
 * furniture is allowed to become architecturally "too innocent to be a Mimic".
 */
final class FurnitureCatalogue
{
    public function __construct(
        private ?WordPressCustomFurnitureRepository $custom = null
    ) {}

    /** @return array<string,array<string,mixed>> */
    public function builtIns(): array
    {
        return [
            'table' => $this->definition(
                'Table',
                SceneObjectCategory::STRUCTURAL,
                2.0,
                1.0,
                true,
                'half',
                false,
                0.45,
                'none',
                'A sturdy dungeon table. Number of legs not contractually guaranteed.'
            ),
            'chair' => $this->definition(
                'Chair',
                SceneObjectCategory::DECORATIVE,
                0.75,
                0.75,
                false,
                'none',
                false,
                0.15,
                'none',
                'A suspiciously conventional place to sit.'
            ),
            'chest' => $this->definition(
                'Chest',
                SceneObjectCategory::INTERACTIVE,
                1.0,
                0.75,
                true,
                'half',
                false,
                0.55,
                'open_close',
                'Storage, treasure, or an extremely poor life decision.'
            ),
            'barrel' => $this->definition(
                'Barrel',
                SceneObjectCategory::STRUCTURAL,
                0.9,
                0.9,
                true,
                'half',
                false,
                0.55,
                'none',
                'A stout barrel for provisions, brine, or ominous silence.'
            ),
            'crate' => $this->definition(
                'Crate',
                SceneObjectCategory::STRUCTURAL,
                1.0,
                1.0,
                true,
                'three_quarters',
                false,
                0.70,
                'none',
                'A stackable wooden crate with absolutely no promises about contents.'
            ),
            'bookshelf' => $this->definition(
                'Bookshelf',
                SceneObjectCategory::STRUCTURAL,
                1.5,
                0.6,
                true,
                'full',
                true,
                1.00,
                'none',
                'A shelf of books, ledgers, maps and future bad ideas.'
            ),
            'bed' => $this->definition(
                'Bed',
                SceneObjectCategory::STRUCTURAL,
                2.0,
                1.0,
                true,
                'half',
                false,
                0.35,
                'none',
                'A narrow adventurer-sized bed. Pippin has checked beneath it once.'
            ),
            'desk' => $this->definition(
                'Desk',
                SceneObjectCategory::STRUCTURAL,
                1.5,
                0.8,
                true,
                'half',
                false,
                0.45,
                'none',
                'A writing desk for maps, ledgers, warrants and increasingly worried notes.'
            ),
            'bench' => $this->definition(
                'Bench',
                SceneObjectCategory::STRUCTURAL,
                2.0,
                0.6,
                true,
                'half',
                false,
                0.30,
                'none',
                'A long wooden bench. Seating capacity depends on optimism.'
            ),
            'cupboard' => $this->definition(
                'Cupboard',
                SceneObjectCategory::STRUCTURAL,
                1.2,
                0.7,
                true,
                'full',
                true,
                0.95,
                'none',
                'A tall cupboard whose contents are between the Keeper and the cupboard.'
            ),
            'sacks' => $this->definition(
                'Sacks',
                SceneObjectCategory::DECORATIVE,
                1.0,
                0.8,
                false,
                'half',
                false,
                0.25,
                'none',
                'A heap of provisions, flour, grain, or something with paperwork.'
            ),
            'weapon-rack' => $this->definition(
                'Weapon Rack',
                SceneObjectCategory::STRUCTURAL,
                1.5,
                0.5,
                true,
                'half',
                false,
                0.35,
                'none',
                'A rack of pointy occupational equipment.'
            ),
            'market-stall' => $this->definition(
                'Market Stall',
                SceneObjectCategory::STRUCTURAL,
                2.0,
                1.5,
                true,
                'three_quarters',
                false,
                0.65,
                'none',
                'A portable stall ready for commerce, haggling and suspicious turnips.'
            ),
            'campfire' => $this->definition(
                'Campfire',
                SceneObjectCategory::INTERACTIVE,
                1.0,
                1.0,
                true,
                'half',
                false,
                0.15,
                'none',
                'A contained campfire. It is furniture only because nobody volunteered to argue.'
            ),
            'stool' => $this->definition(
                'Stool',
                SceneObjectCategory::DECORATIVE,
                0.65,
                0.65,
                false,
                'none',
                false,
                0.10,
                'none',
                'A small stool for sitting, reaching shelves, or regrettable improvised tactics.'
            ),
            'rug' => $this->definition(
                'Rug',
                SceneObjectCategory::DECORATIVE,
                2.0,
                1.5,
                false,
                'none',
                false,
                0.0,
                'none',
                'A woven rug that blocks absolutely nothing except stains.'
            ),
        ];
    }

    /** @return array<string,array<string,mixed>> */
    public function all(): array
    {
        $builtIns = $this->builtIns();
        $custom = ($this->custom ?? new WordPressCustomFurnitureRepository())->all();

        // Core keys are deliberately immutable. A custom record may never
        // shadow a certified built-in furnishing.
        return $builtIns + $custom;
    }

    /** @return array<string,mixed>|null */
    public function find(string $kind): ?array
    {
        $kind = sanitize_key($kind);
        return $this->all()[$kind] ?? null;
    }

    public function isBuiltIn(string $kind): bool
    {
        return array_key_exists(sanitize_key($kind), $this->builtIns());
    }

    /** @return array<string,mixed> */
    private function definition(
        string $label,
        string $category,
        float $widthUnits,
        float $heightUnits,
        bool $blocksMovement,
        string $cover,
        bool $blocksVision,
        float $lightOcclusion,
        string $interaction,
        string $description
    ): array {
        return [
            'label' => $label,
            'category' => $category,
            'width_units' => $widthUnits,
            'height_units' => $heightUnits,
            'description' => $description,
            'blocks_movement' => $blocksMovement,
            'cover' => $cover,
            'blocks_vision' => $blocksVision,
            'light_occlusion' => max(0.0, min(1.0, $lightOcclusion)),
            'interaction' => in_array($interaction, ['none', 'open_close'], true)
                ? $interaction
                : 'none',
            'mimic_capable' => true,
        ];
    }
}
