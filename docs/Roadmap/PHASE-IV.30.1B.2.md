# Phase IV.30.1B.2 — Contour Simplification & Full-Boundary Tracing

## Purpose

Fine Contour Sampling proved that Living Contour can visually hug irregular cave artwork, but the advanced cave benchmark exposed a second bottleneck: thousands of tiny fine-mesh boundary strokes were being sorted and truncated to the first 200 review suggestions. Because those strokes were discovered in scan order, the upper cave perimeter could look excellent while the lower map received no draft at all.

This corrective keeps the finer analysis resolution from IV.30.1B.1 and changes the order of operations: **trace the complete boundary first, simplify it second, enforce the review budget last**.

## Behaviour

Living Contour now builds endpoint adjacency across every detected floor/solid boundary and traces maximal connected chains plus closed contour cycles. Complete paths are simplified with a Douglas-Peucker-style tolerance so long runs of tiny strokes become useful LOS segments while meaningful bends remain represented.

The simplification tolerance starts conservatively from the fine-mesh scale and increases only when needed to fit the existing 200-suggestion review ceiling. Every connected contour participates in every simplification pass. There is no raw `.slice(0, 200)` on Living Contour output, so scan order cannot silently spend the entire budget on the top of the map.

If exceptionally fragmented artwork still cannot fit the safe review budget after bounded simplification, the Assistant returns no incomplete contour and asks the Keeper to check calibration or use Structural tracing. Nothing is saved automatically; the Keeper still reviews the private draft and explicitly chooses **Apply Selected**.

## Benchmark contract

- **Control — regular dungeon:** Structural tracing remains the preferred mode and must not regress.
- **Advanced — cave dungeon:** Living Contour should cover the full traversable perimeter with connected, simplified segments rather than only the first scanned region.
- **Hostile — misaligned-grid dungeon:** remains reserved for later grid-registration work; this phase does not alter gameplay-grid calibration.

## Safety boundary

The calibrated gameplay grid remains authoritative for movement, scale and range. Fine contour coordinates may remain fractional gameplay-grid units, but accepted barriers still use the existing server-authoritative Vision Barrier pipeline and its 200-suggestion batch ceiling.
