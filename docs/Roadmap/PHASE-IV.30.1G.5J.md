# Phase IV.30.1G.5J — Playable Surface Reconstruction & Occlusion Recovery

## The Mushroom Has Not Eaten the Floor

The Dungeon from Hell exposes a different failure from a broken contour: map illustration can hide the floor itself. A mushroom, creature, rubble field, annotation, or dense hatch may interrupt the fine floor mask even though the playable surface continues underneath it.

G.5J therefore operates before boundary-side classification. It searches only for short occluded spans bracketed by visible playable floor. Recovery additionally requires lateral floor support and rejects spans dominated by sustained dark structural ink. Accepted cells become reconstructed playable-surface evidence; they are not walls and do not directly create pink geometry.

Hybrid Judgement may make one additional conservative recovery pass after its connected-floor preparation. Standalone Living Contour remains single-pass. Both feed the recovered surface into the existing G.5A–G.5I semantic, adjacency, graph, closure, and perimeter reasoning.

The safety rule remains simple: artwork may obscure the floor, but evidence must prove continuity before Pippin puts it back.

## G.5J.1 — Monotonic Recovery Correction

Dungeon-from-Hell runtime QA exposed an important composition regression: reconstructed
surface evidence could change component/exterior reasoning enough to revoke contours that
G.5A–I had already certified. Living Contour could consequently return no safe sections,
while Hybrid could become substantially sparser.

G.5J is now explicitly monotonic. Each Living Contour invocation preserves a pre-recovery
G.5I-equivalent certification baseline. Occlusion recovery is evaluated as supplementary
evidence; its results are merged into that baseline within the existing 200-object review
budget. Failure to recover an occlusion, an empty post-recovery chain set, or an over-budget
post-recovery result therefore falls back to the already-certified baseline rather than
invalidating it.

**Invariant:** playable-surface reconstruction may add evidence, never subtract certified
boundary evidence.
