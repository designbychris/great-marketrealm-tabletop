# Phase IV.30.1C — The Cartographer's Linework / Polyline Vision Barriers

## Purpose

Living Contour can now see and trace useful cave boundaries, but the earlier authoritative model stored every bend as an independent two-point `VisionBarrier`. The Advanced cave benchmark repeatedly proved that a good contour could exceed the 200-object review ceiling even when the geometry itself was sound.

IV.30.1C changes the size of the authoritative cartographic object instead of weakening the contour. A wall may now be an ordered polyline containing many connected vertices. Existing two-point walls and doors remain fully compatible.

## Authoritative geometry

A persisted wall path owns an ordered `points` collection. Each adjacent pair is a normal line-of-sight span:

`A → B → C → D`

is one cartographic object but resolves sight as `A→B`, `B→C`, and `C→D`.

Doors deliberately remain two-point objects so their open/closed state retains the established Sight Beyond the Door semantics.

The model keeps `x1/y1/x2/y2` as the path's first and final points for backwards compatibility. Old repository records without `points` reconstitute as ordinary two-point barriers.

## Living Contour contract

Living Contour still performs Fine Contour Sampling, complete connected-chain/cycle tracing, and topology-safe simplification. The review budget now counts **complete wall paths**, not every internal span.

Each proposed path is bounded to 256 vertices. A single Assistant apply remains bounded to 200 review objects and 6,000 total path vertices. Nothing is saved automatically; the Keeper still reviews the private draft and explicitly chooses **Apply Selected**.

This means a cave perimeter can retain dozens of meaningful bends while consuming one authoritative wall-path record rather than dozens of independent records.

## Rendering and LOS

The Keeper's vision layer renders multi-vertex walls as SVG polylines. The roster labels them as wall paths and reports their connected span count. Removal remains object-level: removing a path removes that complete persisted contour object.

`SightLineResolver` tests every consecutive span returned by the barrier model, so Fog of War and exploration memory continue to use the same blocker-aware sight system rather than a parallel cave-only resolver.

## Safety and compatibility

- Existing two-point wall and door records remain valid.
- Doors cannot be constructed as multi-vertex paths.
- Duplicate consecutive vertices are removed during normalisation.
- A path requires at least two distinct vertices.
- One path is capped at 256 vertices.
- One Cartography Assistant apply is capped at 200 objects and 6,000 total vertices.
- Keeper/Scene authority and Behind-the-Curtain preparation remain server-authoritative.
- The hostile misaligned-grid benchmark remains reserved for later grid-registration work.

## Browser certification target

Re-run the Advanced cave benchmark at its correct gameplay calibration. Success is no longer “compress every cave bend under 200 segments.” Instead, the private draft should show complete local wall-hugging polylines across the cave without giant cross-room chords or a safe-review-budget failure merely because one contour contains many bends.
