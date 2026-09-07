# Phase IV.35.4C.2B — Pippin Measures the Shadow Twice

Corrective for generated/calibrated Scenes.

Scene Object vision and light-occlusion geometry must use the same scaled grid
contract as `FogCellMapper`. Raw `grid_size` values are calibrated against
`grid_reference_width`; using them without the scale displaced/oversized
furniture blockers and could make a moved object appear to keep blocking a ray.

This corrective:

- aligns light-occluder footprints with Fog calibration;
- aligns Scene Object vision blocker footprints and target-cell centres;
- retains environmental light markers when their source cell is either in
  direct viewer LOS or already in final authoritative visibility;
- does not change furniture persistence or the core light catalogue.
