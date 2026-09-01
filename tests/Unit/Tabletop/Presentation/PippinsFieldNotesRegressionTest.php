<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\Presentation;

use PHPUnit\Framework\TestCase;

final class PippinsFieldNotesRegressionTest extends TestCase
{
    private function source(string $path): string
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/' . $path);
        self::assertIsString($source);

        return $source;
    }

    public function test_both_canonical_pippin_artworks_are_packaged(): void
    {
        $root = dirname(__DIR__, 4);

        self::assertFileExists($root . '/assets/images/pippin-peppercorn-cartographer.png');
        self::assertFileExists($root . '/assets/images/pippin-peppercorn-pixel.png');
    }

    public function test_forge_contains_pippins_survey_desk_and_field_note(): void
    {
        $view = $this->source('app/Tabletop/Views/chamber.php');

        self::assertStringContainsString('gmrt-pippin-desk', $view);
        self::assertStringContainsString('Meet the Wandering Cartographer', $view);
        self::assertStringContainsString('data-pippin-field-note', $view);
        self::assertStringContainsString('data-pippin-field-note-copy', $view);
        self::assertStringContainsString('pippin-peppercorn-cartographer.png', $view);
        self::assertStringContainsString('pippin-peppercorn-pixel.png', $view);
    }

    public function test_field_notes_are_contextual_to_scene_type(): void
    {
        $javascript = $this->source('assets/js/tabletop.js');

        self::assertStringContainsString('const pippinNotes = {', $javascript);
        self::assertStringContainsString("dungeon: 'Walls are walls. Unless they are Mimics.", $javascript);
        self::assertStringContainsString("forest: 'I have personally confirmed that trees are not rooms.", $javascript);
        self::assertStringContainsString("village: 'Buildings confirmed to be rooms.", $javascript);
        self::assertStringContainsString("atlasForgeSceneType?.addEventListener('change'", $javascript);
    }

    public function test_field_note_uses_the_pixel_ui_vocabulary(): void
    {
        $css = $this->source('assets/css/tabletop.css');

        self::assertStringContainsString("Phase IV.32.2A — Pippin's Field Notes.", $css);
        self::assertStringContainsString('.gmrt-pippin-note__portrait', $css);
        self::assertStringContainsString('image-rendering: pixelated;', $css);
        self::assertStringContainsString('.gmrt-pippin-note__bubble::before', $css);
        self::assertStringContainsString('.gmrt-pippin-note.is-speaking', $css);
    }

    public function test_full_illustration_is_lazy_loaded_and_pixel_portrait_is_decorative(): void
    {
        $view = $this->source('app/Tabletop/Views/chamber.php');

        self::assertStringContainsString('loading="lazy"', $view);
        self::assertStringContainsString('class="gmrt-pippin-note__portrait"', $view);
        self::assertStringContainsString('aria-hidden="true"', $view);
    }
}
