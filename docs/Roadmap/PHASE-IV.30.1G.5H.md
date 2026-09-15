# Phase IV.30.1G.5H — Boundary Graph Reconstruction & Evidence Bridging

## Pippin Reconstructs the Broken Wall

The Dungeon from Hell has shown that correct local classification is not enough when a genuine region perimeter is fragmented into several independently useful contour chains. G.5H lets Living Contour reason about those fragments as a **boundary graph** rather than as unrelated lines.

A graph edge is not granted merely because two endpoints are nearby. Candidate ends must belong to structural-wall chains, approach one another with compatible tangents, preserve playable/non-playable boundary-side separation, and avoid playable floor running through the proposed bridge. Ends already carrying certified portal or inferred-threshold connectivity are excluded before bridging is considered.

Certified graph bridges are emitted as small explicit contour polylines with their own evidence metadata. They do not silently close a contour, consume a doorway, or turn proximity into authority. Ambiguous gaps remain open for Keeper review.

**Cartographer's rule:** proximity is evidence; it is never permission.
