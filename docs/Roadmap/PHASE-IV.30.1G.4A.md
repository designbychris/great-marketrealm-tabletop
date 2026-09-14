# Phase IV.30.1G.4A — Hybrid Partial-Contour Preservation

## The Cartographer Keeps the Gaps

Hybrid Judgement previously asked Living Contour to run in Connected Dungeon mode and could therefore return no safe draft on maps where the standalone Living Contour reader had already certified useful partial boundaries. Heavy hatch, irregular cave ink and narrow thresholds can make the connected-floor pass prove less than the direct reader.

This corrective phase preserves uncertainty instead of discarding evidence.

### Behaviour

- Hybrid still prefers the connected-floor contour pass when it produces certified evidence.
- If that pass produces nothing, Hybrid reuses the standalone Living Contour result as a review-first fallback.
- If structural-overlap trimming removes every connected contour, Hybrid gets one final standalone fallback rather than reporting that nothing is known.
- Partial contours remain partial: their unresolved endpoints are copied through unchanged and are never bridged simply to make a closed wall.
- Organic paths are ranked by confidence and useful path length if pathological fragmentation reaches the 200-object review ceiling; the whole Hybrid draft is no longer rejected solely because of object count.
- Structural linework still wins genuine local overlap, and no suggestion becomes authoritative until the Keeper applies it.

### Non-goals

This phase does not decide whether every unresolved Living Contour gap is a doorway. Door/gap classification remains a separate forensic concern. The important contract here is simpler: **Hybrid may combine what Pippin knows, but it must not erase what Pippin already proved.**
