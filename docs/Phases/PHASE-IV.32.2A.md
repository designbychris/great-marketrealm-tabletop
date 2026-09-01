# Phase IV.32.2A — Pippin's Field Notes

Pippin Peppercorn now has a proper place inside the Keeper's Atlas.

## Purpose

This phase integrates the two canonical Pippin artworks into the Scene Forge without changing Forge topology, persistence, LOS, Fog, lighting, tokens or encounter behaviour.

## The Survey Desk

The full-colour cartographer illustration appears in an optional **Meet the Wandering Cartographer** panel inside Generate Scene. It is lazy-loaded so the Atlas can remain a working tool first and a character moment second.

## Pippin's Field Note

The 16-bit Pippin is used as the portrait for a reusable pixel dialogue component. The note reacts to the selected Scene Type and to Forge actions:

- Dungeon — walls are provisionally accepted as walls, pending Mimic investigation.
- Forest — trees are confirmed not to be rooms; grass remains suspicious.
- Village — buildings are confirmed to be rooms.
- New Seed — Pippin records the deterministic survey principle.
- Forge — Pippin reports that the survey is underway.

## Architectural rule

Pippin is presentation and guidance, not game state. Field Notes never alter the generated plan or any authoritative Tabletop system.

## Assets

- `assets/images/pippin-peppercorn-cartographer.png`
- `assets/images/pippin-peppercorn-pixel.png`

Both are supplied project artwork and are packaged locally with the plugin.
