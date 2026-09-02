# Phase IV.32.4D — The Battlefield's Final Inspection

**Status:** implemented; PHPUnit and browser certification pending.

This phase closes the IV.32.4 battlefield visual pass by making the existing 16-bit layers compose deliberately when they are all present at once.

## Final inspection contract

- The battlefield has one explicit presentation stack: map → grid → footsteps → Vision/light → Living Veil → Keeper thresholds → miniatures → targeting feedback.
- Current targeting feedback stays readable above Fog and tokens because it describes the current action rather than the underlying map.
- Keeper deployment thresholds remain above the Veil but below miniatures, so a marker cannot visually cover a creature occupying the same position.
- Decorative overlays remain pointer-transparent and cannot steal drag, targeting, door, threshold or token input.
- Reduced-motion users keep the same static battlefield hierarchy without decorative animation.

## Authority boundary

This phase is presentation-only. It does not alter rules-grid geometry, token coordinates, movement validation, Fog cells, explored memory, line-of-sight, Vision barriers, door authority, light radii, initiative, targeting calculations, encounter state or persistence. It only makes the already-certified IV.32.4A–C layers compose predictably.
