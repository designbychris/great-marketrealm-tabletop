# Phase IV.35.3A — Pippin Discovers the Table Is Nailed to the Floor

IV.35.2 proved the Scene Object catalogue, persistence and rendering boundaries server-side, but browser certification exposed a familiar battlefield-authoring failure: the furnishing tool armed correctly, yet its late click could be consumed by other battlefield interaction systems before placement completed.

This corrective makes furniture placement follow the already-proven Keeper Lantern Rack interaction contract. While a furnishing is armed, its next primary battlefield pointer is captured on `pointerdown`, propagation is stopped immediately, and map coordinates are resolved through the shared battlemap-aware `coordinatesFromPointer()` helper. Lens panning also explicitly yields while furniture placement is armed.

The phase deliberately does not introduce object manipulation. Selection, moving, rotation, scaling, duplication and deletion remain IV.35.3B. Scene Object persistence, exact live/preparation Scene identity, Mimic-capable metadata, player read-only projection and the existing Living Table heartbeat remain unchanged.

## Certification target

Starting certified baseline: **964 tests / 2,928 assertions**. This overlay adds one focused regression test with eight assertions, for an expected clean-suite target of **965 tests / 2,936 assertions**, assuming no unrelated regression changes. Browser certification remains required after the server suite passes.

Pippin's field note: “The table was not nailed down. The pointer was.”
