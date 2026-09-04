<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\SceneObjects;

use GreatMarketrealmTabletop\Tabletop\SceneObjects\Models\SceneObjectCategory;

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
    /** @return array<string,array<string,mixed>> */
    public function all(): array
    {
        return [
            'table' => $this->definition(
                'Table',
                SceneObjectCategory::STRUCTURAL,
                2.0,
                1.0,
                true,
                'A sturdy dungeon table. Number of legs not contractually guaranteed.'
            ),
            'chair' => $this->definition(
                'Chair',
                SceneObjectCategory::DECORATIVE,
                0.75,
                0.75,
                false,
                'A suspiciously conventional place to sit.'
            ),
            'chest' => $this->definition(
                'Chest',
                SceneObjectCategory::INTERACTIVE,
                1.0,
                0.75,
                true,
                'Storage, treasure, or an extremely poor life decision.'
            ),
            'barrel' => $this->definition(
                'Barrel',
                SceneObjectCategory::STRUCTURAL,
                0.9,
                0.9,
                true,
                'A stout barrel for provisions, brine, or ominous silence.'
            ),
            'crate' => $this->definition(
                'Crate',
                SceneObjectCategory::STRUCTURAL,
                1.0,
                1.0,
                true,
                'A stackable wooden crate with absolutely no promises about contents.'
            ),
            'bookshelf' => $this->definition(
                'Bookshelf',
                SceneObjectCategory::STRUCTURAL,
                1.5,
                0.6,
                true,
                'A shelf of books, ledgers, maps and future bad ideas.'
            ),
        ];
    }

    /** @return array<string,mixed>|null */
    public function find(string $kind): ?array
    {
        $kind = sanitize_key($kind);
        return $this->all()[$kind] ?? null;
    }

    /** @return array<string,mixed> */
    private function definition(
        string $label,
        string $category,
        float $widthUnits,
        float $heightUnits,
        bool $blocksMovement,
        string $description
    ): array {
        return [
            'label' => $label,
            'category' => $category,
            'width_units' => $widthUnits,
            'height_units' => $heightUnits,
            'description' => $description,
            'blocks_movement' => $blocksMovement,
            'mimic_capable' => true,
        ];
    }
}
