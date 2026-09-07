# Phase IV.35.8A — Pippin Reads the Labels

Administrator-defined furniture can now tell Dungeon Forge where it belongs.

## Controlled labels

Custom furniture keeps the IV.35.8 `Available to Dungeon Forge` opt-in and may
also carry any of these controlled labels:

- room purpose: `mess`, `store`, `study`, `treasure`, `quarters`, `camp`, `cache`
- environment: `dungeon`, `village`, `forest`, `outdoor`, `market`

The admin screen uses checkboxes rather than a free-text taxonomy. Empty labels
mean the custom item is never automatically placed, even if Forge is enabled.

## Forge matching

For each generated room Pippin builds a small context from its room role and
scene type. Forest also exposes `outdoor`; village also exposes `market`.
An opted-in custom furnishing is eligible when any of its labels intersects the
context.

Selection remains deterministic from the Forge seed and deliberately sparse:
at most two custom accents are proposed per room, and all proposals still pass
through the existing room-boundary, doorway-clearance, footprint-overlap and
overall-density rules.

Generated custom furniture remains an ordinary Scene Object and now persists
its sanitised SVG plus Forge labels alongside the certified tactical properties.

> Pippin: “The ottoman says ‘quarters’.”
>
> Keeper: “So?”
>
> Pippin: “So I have put it in somebody else’s quarters.”
