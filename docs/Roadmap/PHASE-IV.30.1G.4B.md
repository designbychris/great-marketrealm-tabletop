# Phase IV.30.1G.4B — Gap & Threshold Classification

## The Cartographer Knows an Open Door When He Sees One

Partial Contour Recovery taught Living Contour to keep trustworthy boundary sections even when the imported artwork could not support one perfect closed perimeter. The next failure was subtler: a dark door leaf, lintel, threshold mark, or narrow break in a heavily inked cave could still be absorbed into the contour and rendered as an apparent wall across a real opening.

This phase makes uncertainty explicit instead of allowing contour closure to win by default.

## Principles

- Structural Doorway & Threshold Reasoning remains the source of certified doorway evidence.
- Living Contour may consume that evidence, but does not create a second competing door authority.
- A contour span that overlaps a certified doorway/passage is split and the opening is preserved.
- The two resulting contour ends remain unresolved review evidence; they are never reconnected merely to make a prettier polygon.
- Other unresolved ends are classified as `map-edge`, `noise-gap`, or `uncertain-boundary` for Keeper review.
- Hybrid Judgement passes its already-computed structural threshold evidence back into Living Contour so the two readers agree about protected openings.
- No gap classification writes an authoritative VTT door automatically.

## Review evidence

Recovered Living Contour paths may now carry:

- `thresholdGapProtection`
- `protectedContourGaps`
- `gapClassifications`
- `partialContourRecovery: protected-threshold-split`
- `evidenceModel: living-contour-gap-classification-v5b`

The Keeper's review line also reports protected doorway/passage gaps and the classifications attached to unresolved ends.

## Safety rule

**An opening that Pippin can justify stays open.**

Gap classification is a reason to preserve uncertainty, not permission to invent geometry. Doorway candidates remain review-first suggestions and authoritative doors are still created only through the existing Keeper-approved workflow.
