# Phase IV.30.1A — Curves & Continuity

Curves & Continuity is the precision pass for Structural Cartography. It keeps the IV.30.1 dark-pixel/hatch-rejection model, but stops assuming that useful dungeon architecture is only horizontal or vertical.

Structural analysis now traces four principal orientations: horizontal, vertical, 45-degree down-right and 45-degree up-right. The diagonal passes use perpendicular side-density comparisons just like the original orthogonal pass, so a dark run is favoured when at least one immediate side is materially quieter than the ink boundary. This gives cave walls, angled corridors and hand-drawn irregular architecture a path into the same review-first draft without treating every piece of stone hatching as a wall.

A trace may repair one weak sample when the centre remains moderately dark and its quieter side still supports continuity. This is deliberately conservative: it repairs tiny raster/decorative interruptions, but a clear low-density opening ends the run rather than being automatically sealed. Doors therefore remain a Keeper review concern instead of being guessed into existence by aggressive gap filling.

Artwork traces remain free-position analysis geometry. They are simplified only after tracing into short neighbouring grid-intersection barriers, including diagonal neighbours, because the current authoritative Living Veil model stores integer grid intersections. Curves are therefore represented as connected short segments suitable for line of sight rather than pixel-perfect decorative contours. Nothing is saved automatically; the Keeper still reviews and explicitly applies selected suggestions.

## Browser benchmark tiers

1. **Control — regular dungeon:** mostly orthogonal rooms and corridors. Expect near-complete useful structural coverage with minimal cleanup.
2. **Advanced — cave dungeon:** extensive curves, diagonals, irregular passages and isolated rock. Expect a coherent playable approximation rather than reproduction of every hand-drawn wiggle.
3. **Hostile — misaligned-grid dungeon:** retained from IV.30.1 as a future grid-registration/calibration benchmark. Apparent grid-offset errors must not be used to over-tune the structural tracer.

The acceptance goal is practical Sight Beyond the Door geometry: if a wall visually separates spaces, the Assistant should usually create a reviewable barrier approximation that prevents sight travelling through it, while still leaving the Keeper in control of the authoritative result.
