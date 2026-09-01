# Phase IV.30.2 — The Cartographer's Dungeon Forge

## Purpose

The Dungeon Forge turns the Cartography programme around: instead of interpreting a finished battlemap, Pippin creates the playable geometry first and lets the Tabletop derive the map systems from that shared geometry.

## Keeper workflow

The Keeper chooses a deterministic seed and Compact, Standard or Grand scale, then presses **Forge Draft**. The draft is browser-local and review-first. **New Seed** rerolls without changing the Scene; **Clear Draft** removes the preview. Nothing is saved until **Build Dungeon**.

## Geometry first

A forge plan contains connected rooms and orthogonal corridors on a bounded logical square grid. The same floor topology drives all downstream output:

- persistent Tabletop-native SVG floor/rock artwork;
- the existing calibrated square gameplay grid;
- exterior wall barriers;
- closed door barriers at room thresholds;
- canonical Keeper light placements;
- the existing Living Veil, enabled with exploration reset for the newly forged dungeon.

The Forge deliberately does not call an external image-generation service. The generated dungeon is deterministic from its seed and remains reconstructable from persisted geometry.

## Authority and safety

Only an active Dungeon Master can build a forge plan. The accepted plan is Scene-owned and may be built Behind the Curtain. Wall/door output stays inside the Cartography Assistant's existing 200-object apply ceiling, floor cells and lights are bounded, and server-side validation distrusts all browser geometry. A Scene accepts one forged plan in this first pass so an accidental second build cannot stack duplicate walls/lights over authoritative state.

## Lighting

Forge lights reuse the Keeper's Lantern Rack definitions: Torch 20 ft, Lantern 30 ft, Brazier 60 ft, Candle 10 ft and Magical Light 40 ft bright radii with equal dim bands. New forge lights are lit by default and remain fully tendable from the Lantern Rack after build.

## Future Forge passes

Later passes can add room archetypes, themed palettes, secret passages, stair/threshold suggestions, encounter dressing, stronger door semantics and export/import without changing the geometry-first foundation established here.
