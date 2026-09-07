# Phase IV.35.4C.2A — The Light Was There All Along

Browser corrective for IV.35.4C.2.

- Fix the attenuation-cell key matcher so authoritative `column:row` shadow
  values actually render in the browser.
- Expose Keeper environmental light markers when the viewer has line of sight
  to the source cell, independently of whether that source contributes light
  to the final visible set.
- Preserve environmental light kind, label and lit state in the Fog projection.
- No changes to Scene Object occlusion mathematics or persistence.
