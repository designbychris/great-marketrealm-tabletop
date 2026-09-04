# Phase IV.35.4B — Things You Can Hide Behind

Scene furnishings begin carrying tactical cover rather than behaving as one generic kind of obstacle.

- Scene Objects now expose explicit `cover` and `blocks_vision` traits independently from `blocks_movement`.
- The first catalogue assigns sensible tactical defaults: Chair = none, Table/Chest/Barrel = half cover, Crate = three-quarters cover, Bookshelf = full cover and a vision blocker.
- Existing furnishings placed before this phase inherit current catalogue defaults when their persisted properties predate the tactical traits.
- Targeting tests the attack line against the real rotated/scaled Scene Object footprint and reports the strongest intervening furnishing as HALF COVER, 3/4 COVER or FULL COVER.
- A full vision-blocking furnishing also reports OBSCURED in the targeting status and gives the battlefield targeting line an explicit obstructed presentation.
- Cover remains descriptive in this pass: it does not silently alter Companion-certified AC or attack rolls.
- The server-authoritative Living Veil still owns actual token visibility. Wiring `blocks_vision` into Fog/LOS should be completed as a small IV.35.4B.1 bridge rather than weakening the existing server-side visibility boundary.
- Light occlusion remains reserved for IV.35.4C.

Pippin's field note: “I am not hiding behind the bookshelf. I am conducting a visibility experiment from an administratively sensible location.”
