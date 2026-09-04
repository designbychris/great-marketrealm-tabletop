# Phase IV.35.4A — Things You Cannot Walk Through

## Pippin's complaint

> “No, you cannot stand inside the bookshelf.”

Scene Objects now participate in battlefield movement without yet becoming cover, vision blockers or light occluders.

## Behaviour

- Furniture definitions declare an explicit `blocks_movement` property; collision is not inferred from Decorative/Structural/Interactive category.
- Table, Chest, Barrel, Crate and Bookshelf block movement by default. Chair remains passable in this first pass so small decorative clutter does not make a room unusable.
- Newly placed objects persist the movement-blocking property with their other Scene Object properties.
- Previously placed catalogue furniture is backfilled at render time from the current catalogue definition, so existing tables/bookshelves immediately participate without a migration.
- Token drag, click-to-move and keyboard movement all share the same collision gate.
- Collision uses the rendered token footprint against the rotated/scaled furnishing footprint rather than a point-only test.
- Drag movement sweeps the route in short increments, preventing a token from teleporting through a blocker merely because its final destination is clear.
- A blocked drag remains at its last legal point and receives a short visual warning.

## Boundaries

This phase is deliberately movement-only. Cover belongs to IV.35.4B; vision and light interaction remain separate so object behaviour can evolve independently. No new movement poller or second Scene Object persistence system is introduced.
