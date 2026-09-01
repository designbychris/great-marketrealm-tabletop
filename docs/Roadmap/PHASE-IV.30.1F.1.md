# Phase IV.30.1F.1 — The Surveyor Learns the Difference Between a Grid and a Wall
## Printed-Grid Signal Discrimination Corrective

The first Registration pass correctly found repeated square-like structure, but the hostile benchmark exposed an important ambiguity: thick architectural walls can repeat more strongly than the faint printed grid underneath them. Pippin therefore needs to identify the *kind* of linework, not merely reward the strongest periodic ink.

This corrective biases registration toward the visual fingerprint of a printed grid: **thin, pale strokes inside otherwise quiet floor areas that repeat on both axes**. Dark wall cores and hatch beds are deliberately down-weighted. A candidate line earns more support when the pixels immediately beside it recover toward the surrounding floor tone, which helps distinguish a one-pixel/antialiased grid stroke from a thick room boundary.

The periodicity search also refuses sparse large-square combs with too few crossings. If a large candidate is still strongest because room walls recur every two, three, four, five or six printed squares, Pippin checks bounded sub-harmonics and prefers the smaller fundamental spacing only when meaningful evidence survives on **both** X and Y. Weak sub-harmonics are ignored so the detector does not manufacture a tiny grid from texture noise.

Registration remains **preview-only**. The existing Calibrate Grid controls receive the suggested size and X/Y offset, but nothing becomes authoritative until the Keeper visually confirms the overlay and presses **Save Grid**.

### Certification benchmark

The hostile hand-drawn dungeon used during IV.30.1F is the primary browser regression: its faint small-square printed grid must beat the much darker room-wall cadence. The desired result is a useful approximation of those smaller squares rather than a grid sized to whole rooms.
