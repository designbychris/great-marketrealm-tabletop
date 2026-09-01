# Phase IV.30.1F — The Cartographer's Registration
## Printed-Grid Registration & Calibration Intelligence

Pippin may now inspect the already-loaded battlemap in the Keeper's browser and suggest the square size plus X/Y registration of a printed artwork grid before Cartography analysis begins.

The pass measures repeated thin horizontal and vertical line evidence independently, searches for a square periodicity supported on both axes, and chooses the equivalent X/Y phase nearest the Keeper's current calibration so the preview does not jump unnecessarily. Heavy hatching, handwriting and isolated walls may contribute local ink, but they do not by themselves satisfy the repeated two-axis square-grid contract.

A detected registration is **preview-only**. It updates the existing Calibrate Grid controls and live overlay, but does not call a server mutation route. The Keeper must visually inspect the suggestion and press the existing **Save Grid** button before `gmrt_calibrate_grid` persists anything authoritative.

The detector fails conservatively when repeated square-grid evidence is weak. Manual calibration remains available and no Cartography, Fog, token, Scene or LOS state is changed by a failed or merely previewed registration.

### Certification benchmark

The **Hostile misaligned-grid benchmark** is the primary browser test: artwork whose baked-in square grid is visibly offset from the Tabletop overlay should receive a useful suggested square size and X/Y phase, after which Structural / Judgement analysis can run against the corrected gameplay registration rather than being tuned around a bad alignment.

This phase remains browser-local and dependency-free; it does not require OpenCV, Composer image processing, an external service or a second authoritative grid model.
