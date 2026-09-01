# Phase IV.31 — The Keeper's Lantern Rack

The Dungeon Master can place Scene-owned environmental light sources directly on the battlemap. Torch, Lantern, Brazier, Candle and Magical Light presets reuse the existing server-authoritative Living Veil illumination pipeline.

Lights are Scene-scoped, may be prepared Behind the Curtain, respect vision barriers through the existing fog projector, and can be doused or removed by the Keeper. Player projections receive only light sources already safe to reveal through the existing Fog of War projection.

Placement is Keeper intent only: choose a source, click the map, and persist through the DM-only AJAX controller. No parallel lighting engine is introduced.
