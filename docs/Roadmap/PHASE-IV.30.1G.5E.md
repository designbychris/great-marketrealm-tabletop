# Phase IV.30.1G.5E — Boundary Role & Interior Feature Classification

## Pippin Learns What the Line Is For

G.5E separates a strong ink contour from the gameplay role that contour actually serves. A hill, rubble island, stair mark or decorative enclosure can be visually convincing without being a sight-blocking wall.

### Behaviour

- Living Contour classifies ordered chains as playable-region perimeter, enclosed region boundary, partial perimeter, interior feature, terrain/elevation, interior obstacle, decoration/noise, or unresolved evidence.
- Compact enclosed marks surrounded by playable floor remain evidence but are not promoted into automatic LOS walls.
- Terrain/elevation boundaries with playable floor on both sides are suppressed from automatic wall output.
- Only chains classified as structural region boundaries enter automatic Living Contour wall suggestions.
- G.5D chain certification cannot promote an interior feature merely because it is long or visually coherent.
- Existing doorway and threshold protection remains authoritative.

### Visual acceptance target

On the Dungeon From Hell, Living Contour and Hybrid should retain genuine cave/room perimeters while materially reducing pink outlines around hills, rubble, rocks and other interior artwork.
