# Phase IV.30.1G.1 — Corners, Junctions & Wall Bodies
## Architectural Topology Evidence

IV.30.1G taught Pippin that wall ink should be judged relative to the local map rather than by a single global darkness threshold. IV.30.1G.1 asks the next question: **does that ink behave like architecture?**

A stray annotation stroke, chair leg or piece of rubble can look locally wall-like in isolation. Genuine dungeon architecture is more often connected to other architecture. The Structural reader now builds a lightweight graph from its snapped barrier proposals and scores each segment using the relationships at both endpoints.

The topology layer recognises four forms of supporting evidence:

- **Corners** — a segment meets one non-collinear neighbour, forming an L-like turn.
- **Junctions** — an endpoint participates in a T/X-like meeting with multiple neighbours.
- **Continuation** — a segment continues collinearly into another Structural proposal.
- **Wall-body support** — the midpoint retains ink through an inner normal band while at least one outer side becomes comparatively quiet, supporting the interpretation of a thicker architectural body rather than a single floating mark.

These signals raise confidence; they do not create barriers by themselves. Conversely, a segment whose two endpoints are both isolated and which has no wall-body support receives a modest confidence penalty. It is **not deleted**. Old scans, broken linework and damaged walls can still be legitimate, so topology remains evidence rather than authority.

The resulting suggestions carry their architectural evidence into the review model (`corner`, `junction`, `continuation`, `wall-body`, or `isolated-stroke`) and advance the evidence marker to `local-contrast-topology-v2`. Hybrid Judgement automatically benefits because its existing Structural-confidence decisions now receive better architectural context.

The Keeper review/apply boundary is unchanged. Nothing from this pass writes a wall automatically.

### Why this comes before doorway reasoning

A doorway is much easier to recognise once Pippin understands that the wall fragments on either side belong to a larger architectural run. IV.30.1G.1 therefore intentionally stops at connected structure. IV.30.1G.2 can use these supported wall ends to distinguish deliberate openings from random bright gaps.
