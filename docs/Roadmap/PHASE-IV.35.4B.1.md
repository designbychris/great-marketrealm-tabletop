# Phase IV.35.4B.1 — The Bookshelf Blocks the View

Scene Object vision traits now cross the deliberate boundary into the server-authoritative Living Veil.

- `blocks_vision` furnishings are read from the existing Scene Object repository by exact Table + Scene identity.
- A dedicated `SceneObjectVisionProjector` converts each qualifying furnishing's persisted centre, catalogue footprint, rotation and scale into a rotated sight-blocking polygon.
- Older Scene Objects continue inheriting current catalogue defaults, so existing Bookshelves become sight blockers without a data migration.
- Character sight cells produced by the existing Fog/LOS mapper are filtered server-side before the Chamber decides which non-character tokens a Player may receive.
- The extended long-range viewer LOS used for shared illumination is filtered by the same Scene Object geometry, so illumination behind a Bookshelf does not grant the viewer sight through it.
- The obstacle's own cell remains visible; cells beyond it are the ones removed from sight.
- Furnishings are not copied into the hand-authored Vision Barrier repository. Scene Objects remain Scene Objects.
- IV.35.4C still owns physical light occlusion/attenuation. This bridge does not make the Bookshelf cast or soften light; it only prevents a viewer seeing through it.

Pippin's field note: “I knew there was something behind the bookshelf. I merely object to the suggestion that knowing this should allow me to see it.”
