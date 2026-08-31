# Phase IV.30.1B.3A — Contour Topology Guard

The Cartographer's Economy proved that Living Contour can budget the whole cave, but the small-cave browser benchmark exposed a dangerous simplification failure: a high per-contour tolerance could replace a long winding boundary span with a giant straight chord across playable space, including cross-map diagonals.

This corrective keeps adaptive whole-map budgeting and adds a topology guard around every proposed simplification span.

## Local replacement contract

A simplified cave segment may replace only an ordered span from the same traced contour, and that replacement must remain local to the gameplay scale. The guard therefore requires all of the following before Douglas-Peucker may collapse a span to its endpoints:

- the replacement chord is no longer than six gameplay-grid units;
- both endpoints remain inside the analysed grid envelope;
- the original boundary never deviates more than 0.8 gameplay-grid units from the proposed chord;
- the travelled boundary length is no more than 1.75 times the direct chord length.

These constraints allow Pippin to ignore tiny ink wiggles while preventing a winding room/cave perimeter from becoming a shortcut through playable floor.

## Zero-deviation loophole

A perfectly straight boundary can have zero perpendicular error regardless of its length. If such a span exceeds the topology chord limit, it is split at its midpoint and simplified recursively. This preserves long straight cave walls without ever manufacturing an unbounded review segment.

## Safety and authority

- The 200-suggestion review budget remains unchanged.
- Full-boundary tracing and Adaptive Contour Reduction remain the normal Living Contour path.
- Fine Contour Sampling and fractional Vision Barrier endpoints remain unchanged.
- Nothing is saved automatically; the Keeper must review and use Apply Selected.
- If the complete topology-safe contour still cannot fit the review budget, the Assistant fails closed rather than reintroducing unsafe chords or scan-order truncation.

## Browser benchmark

Re-run the same correctly calibrated 35px small-cave benchmark that exposed the giant diagonals. Success means the pink draft continues to cover the cave broadly while every suggested segment remains local to the boundary it represents, with no cross-room or cross-map chords.
