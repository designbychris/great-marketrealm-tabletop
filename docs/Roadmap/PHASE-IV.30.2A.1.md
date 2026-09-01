# Phase IV.30.2A.1 — Pippin Decorates the Place

The Cartographer’s Dungeon Forge now decorates its generated SVG battlefields instead of treating themes as colour palettes alone.

Each Great Marketrealm theme receives a deterministic visual vocabulary derived from the same seed: Pantry Stone gains worn cracks and pits; Butcher Cellar gains masonry seams and occasional drains; Rootland Cavern gains roots and pebbles; Frostreem Vault gains ice fractures and frost chips; Bakery Crypt gains offset brickwork and crumbs; Mushroom Grotto gains fungal caps and spores. Boundary-adjacent solid rock also gains restrained rock-face marks.

Decoration is presentation-only. It does not mutate the generated floor cells, room topology, vision barriers, closed doors, calibrated grid, Fog of War, or Keeper light coordinates. The same seed and scale therefore remain the same dungeon regardless of theme.

Generated Scenes also explicitly suppress image-backed battlemap presentation, preventing stale map-image artefacts from appearing over a native Forge surface.
