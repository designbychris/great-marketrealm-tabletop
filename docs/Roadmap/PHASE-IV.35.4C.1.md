# Phase IV.35.4C.1 — The Shape of Darkness

Pippin has established that furniture may now obstruct movement, provide cover, and block sight. He has reluctantly accepted the next question: how much light survives the same furniture?

## Goal

Create the reusable Scene Object light-occlusion vocabulary and geometry seam without replacing or duplicating the existing Lantern Rack / Living Veil lighting engine.

## Behaviour

- Every catalogue furnishing declares an independent `light_occlusion` value from `0.0` (transparent to light) through `1.0` (fully opaque).
- Initial defaults are deliberately distinct from movement and vision: Chair 0.15, Table 0.45, Chest/Barrel 0.55, Crate 0.70, Bookshelf 1.00.
- New placements persist the value while older Scene Objects backfill from their current catalogue definition.
- `SceneObjectLightOcclusionProjector` turns the real rotated/scaled Scene Object footprint into normalised polygons and calculates cumulative opacity along a light ray.
- Multiple partial blockers compound transmission rather than collapsing to a single strongest value.
- No new light source, light poller, Fog projector, or client-side authority is introduced.

## Boundary

This phase defines **the shape and strength of darkness**, not the final visual shadow rendering. IV.35.4C.2 will connect this reusable projection to the existing authoritative environmental/dropped/magical/carried illumination flow. Animated pixel emitters remain a presentation follow-up after the mechanical shadow path is stable.

Pippin's field note: “Apparently darkness now has geometry. I had hoped it was merely the absence of paperwork.”
