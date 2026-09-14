# Phase IV.30.1G.4 — Partial Contour Recovery

## Pippin Marks What He Knows

Living Contour previously behaved too much like an all-or-nothing proof: if noisy artwork split one playable boundary into too many independent contour chains, the reader could discard the entire draft rather than risk crossing an uncertain gap.

This phase changes that contract. The Assistant may now preserve the strongest safe contour sections even when the complete boundary cannot be certified.

### Behaviour

- Closed contour chains remain high-confidence full-boundary suggestions.
- Open but ordered contour chains may be emitted as **partial contours** when they are long enough to be useful.
- Partial contours retain their open endpoints as `unresolvedBoundaryEnds`; the Assistant never joins them merely to close the shape.
- When a noisy map produces more than the 200 review-object safety budget, the strongest closed/long contour sections are ranked and retained instead of failing the entire Living Contour pass.
- Partial contours are slightly lower confidence than certified closed boundaries and are visibly identified in Keeper review.
- Hybrid Judgement accepts safe partial organic paths alongside structural evidence without promoting their unresolved gaps into walls or doors.
- The Keeper remains authoritative: nothing is persisted until **Apply Selected**.

### Safety rule

**Missing evidence is not permission to invent a wall.** An unresolved contour end stays unresolved until later threshold/gap reasoning or Keeper judgement can explain it.

### Next

**IV.30.1G.4A — Unresolved Gap & Threshold Classification** should classify partial-contour endpoints as likely doorway, map edge, noise break or genuinely uncertain boundary before any optional bridging is considered.
