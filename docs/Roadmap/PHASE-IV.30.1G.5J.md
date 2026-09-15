# Phase IV.30.1G.5J — Playable Surface Reconstruction & Occlusion Recovery

## The Mushroom Has Not Eaten the Floor

The Dungeon from Hell exposes a different failure from a broken contour: map illustration can hide the floor itself. A mushroom, creature, rubble field, annotation, or dense hatch may interrupt the fine floor mask even though the playable surface continues underneath it.

G.5J therefore operates before boundary-side classification. It searches only for short occluded spans bracketed by visible playable floor. Recovery additionally requires lateral floor support and rejects spans dominated by sustained dark structural ink. Accepted cells become reconstructed playable-surface evidence; they are not walls and do not directly create pink geometry.

Hybrid Judgement may make one additional conservative recovery pass after its connected-floor preparation. Standalone Living Contour remains single-pass. Both feed the recovered surface into the existing G.5A–G.5I semantic, adjacency, graph, closure, and perimeter reasoning.

The safety rule remains simple: artwork may obscure the floor, but evidence must prove continuity before Pippin puts it back.
