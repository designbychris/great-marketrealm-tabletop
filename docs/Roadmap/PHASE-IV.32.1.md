# Phase IV.32.1 — The Keeper Finds the Pixel Chisel

Phase IV.32.1 establishes the first reusable SNES-era visual foundation for the Great Marketrealm Tabletop.

## Contract

The Pixel Chisel is presentation-only. It does not alter encounter state, token coordinates, generated Scene topology, Vision/Fog geometry, light ranges, grid registration, or any other rules behaviour.

## First-pass vocabulary

The Tabletop shell now shares a compact pixel vocabulary built from CSS custom properties: dark timber/ink surfaces, parchment text, stepped brass borders, hard unblurred shadows, square controls, monospace UI labels, pixel-like pressed states, and explicit keyboard focus treatment.

The first pass applies that vocabulary to the chamber masthead, battlefield and party panels, battlefield frame, Cartographer/Fog/Vision/Lantern control strips, encounter/exploration strips, common form controls, party rows, HP tracks, and the battlefield Lens controls.

## Accessibility

Keyboard focus remains explicit and high contrast. Disabled states remain visually distinct. The small button press motion is removed when `prefers-reduced-motion` is enabled.

## Scope boundary

This is the foundation, not the final art pass. Atlas drawers, Bestiary/adventurer drawers, encounter widgets, battlefield effects, tokens, Fog, doors, lights, and Forge scenery receive dedicated pixel passes in later IV.32 phases so browser feedback can shape one visual stratum at a time.
