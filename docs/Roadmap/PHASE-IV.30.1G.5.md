# Phase IV.30.1G.5 — Boundary-Side & Wall-Band Reasoning

## Pippin Chooses the Floor-Facing Edge

The Dungeon From Hell exposed a new ambiguity after partial contours and protected thresholds became reliable: thick cave walls are illustrated as bands of crosshatching. Both sides of that band can produce strong dark edges, so a contrast-only reader may jump from the playable-floor edge to the exterior edge and continue confidently on the wrong side of the rock.

This phase gives Living Contour a conservative notion of **wall side**.

## Principles

- A border-connected white region is not automatically playable floor.
- Only a large, substantially border-connected floor component is treated as likely exterior whitespace; a real entrance may touch the image edge without being discarded.
- A candidate wall edge must have sustained playable-floor depth behind its inward side.
- Tiny white pockets between hatch strokes are not enough evidence to become the floor-facing side of a wall.
- Once the floor-facing side is established, Hybrid inherits that Living Contour geometry rather than choosing the visually stronger far edge of the same wall band.
- No wall is invented to close an uncertain region, and G.4B doorway protection remains authoritative.

## Evidence

Living Contour now records the `living-contour-wall-band-v6` evidence family. Partial and closed paths retain their existing uncertainty semantics while carrying the wall-band decision forward.

## Safety rule

**The wall belongs where the adventurer meets the rock, not wherever the ink happens to be darkest.**

This phase deliberately does not classify hills, pools or other interior terrain contours. Semantic boundary classification follows once Pippin can reliably choose the correct side of a genuine wall band.
