# Phase IV.30.1B.3 — The Cartographer's Economy

## Adaptive Contour Reduction

Living Contour can now see a cave at fine resolution and trace complete connected boundaries, but the Advanced — cave dungeon benchmark exposed a second-order problem: a faithful full contour may still need more than the Keeper's 200-review-suggestion safety budget.

This phase keeps the safe ceiling and changes how Pippin spends it.

### Budget the whole dungeon, not a scan prefix

The Cartographer measures every complete contour chain/cycle first. Very small fine-mesh loops are treated as hatch/ink noise. Each remaining boundary receives a minimum representation cost, then the rest of the 200-segment review budget is distributed across the whole dungeon by square-root perimeter weighting. Large cave walls therefore receive more detail without starving smaller pillars, islands, or secondary boundaries.

### Per-contour adaptive simplification

Each retained chain receives its own target segment count. A bounded binary search finds a Douglas-Peucker tolerance that compresses that contour to its allocation while keeping as many meaningful bends as the budget allows. This replaces the single global tolerance as the normal path.

The older global simplification loop remains only as a defensive numerical fallback. The Assistant still never truncates raw suggestions by scan order.

### Safety and authority

- Maximum review draft remains 200 suggestions.
- Fine Contour Sampling remains browser-local and bounded.
- Nothing is saved automatically; the Keeper must review and use Apply Selected.
- Existing authoritative Vision Barrier persistence and fractional endpoints are unchanged.
- The hostile misaligned-grid benchmark remains reserved for later grid-registration work.

### Benchmark contract

- **Control — regular dungeon:** Structural tracing remains the preferred path.
- **Advanced — cave dungeon:** Living Contour should now distribute useful coverage around the full playable cave instead of failing merely because a single global tolerance could not fit the complete boundary under 200 segments.
- **Hostile — misaligned-grid dungeon:** intentionally deferred to grid registration; this phase must not compensate by distorting gameplay calibration.
