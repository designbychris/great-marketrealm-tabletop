# Phase IV.35.4C.2C — The Walls Would Also Like a Say

## Problem

The authoritative illumination calculation already used the same Vision
Barriers as the Living Veil, but the browser still painted each Keeper light
as an unconstrained radius-sized radial gradient. That presentation could
visibly bleed through a room wall even when the server had correctly rejected
the cells beyond it.

## Corrective

- Reuse `FogCellMapper::visibleAround()` and the existing `SightLineResolver`.
- Publish the surviving barrier-resolved illumination as `light_cells`.
- Players receive only illuminated cells also inside their authoritative viewer
  LOS; the Keeper receives the full barrier-resolved illumination.
- Preserve bright/dim strength and Scene Object transmission in each cell.
- Render warm/cool pixel-cell light pools in the browser.
- Reduce the old radial light graphic to a compact emitter-local halo.
- Preserve IV.35.4C.3 animated flames and IV.35.4C.2 furniture attenuation.

No new wall repository, light barrier model, or CSS clipping authority is added.

> Pippin: “The wall has submitted its opinion. It is 'no'.”
