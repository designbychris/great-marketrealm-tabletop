# Phase IV.30.1G.2 — Doorway & Threshold Reasoning

## The Cartographer Looks Again

Imported battlemaps often draw a doorway as nothing more than an intentional gap in a wall. Earlier Cartography Assistant passes could notice a bright middle section on a dark edge, but brightness alone is not architectural evidence: broken ink, texture, furniture, labels and scanning artefacts can all create similar gaps.

This phase teaches the Structural reader to reason about a threshold **in context**.

## Evidence model

A gap is promoted to a `door` suggestion only when all of these conservative conditions are satisfied:

- a supported Structural wall segment continues immediately before the gap;
- another supported Structural segment continues immediately after it;
- the missing span itself is measurably quieter/lighter than those supporting walls;
- plausible floor is visible on both sides of the threshold;
- the opening is exactly one calibrated grid unit wide in this first pass; and
- the supporting walls retain enough confidence to make the gap meaningful.

Corners, junctions and continuation evidence from IV.30.1G.1 contribute additional confidence, but none of them can create a doorway by themselves.

## Forensic metadata

Threshold drafts carry `thresholdEvidence`, `thresholdSupport`, `doorwayReasoning` and the evidence model identifier `local-contrast-topology-threshold-v3`. The review list reports these reason codes so the Keeper can see *why* Pippin believes the gap is useful.

The supporting measurements include wall continuity confidence, opening contrast, opening ink density, floor samples on both sides, topology support and width in grid units.

## Deliberately conservative scope

This phase only reasons about orthogonal, one-grid thresholds. Wide arches, double doors, diagonal thresholds and symbol recognition remain manual rather than being guessed from weak evidence. Later phases can widen the model once benchmark maps prove that safe.

## Keeper authority

Doorway reasoning remains evidence, not authority. The Assistant never writes a door directly to the Scene. The candidate appears in the existing private review draft and is persisted only if the Keeper selects and applies it. Existing VTT door controls remain authoritative after application.

## Next

**IV.30.1G.3 — Noise, Furniture & Annotation Rejection** can build on this evidence model by explicitly demoting non-architectural strokes before they enter wall/threshold reasoning.
