# Phase IV.30.1G.3 — Noise, Furniture & Annotation Rejection

## The Cartographer Learns What Not to Trace

Imported battlemaps contain much more dark linework than architecture. Labels, stair treads, table and chair outlines, rugs, shelving, rubble, hatch beds and decorative flourishes can all satisfy a naïve “dark line” test. Earlier passes already used quiet-side density and topology to protect real walls; this phase adds an explicit non-architectural evidence layer before doorway reasoning.

## Coarse component evidence

The Structural reader now builds a deliberately coarse connected-component map over the adaptive ink mask. This is not OCR and it does not attempt to name objects. It only asks whether a mark behaves like a short compact annotation, a small closed furnishing-like outline, or a larger architectural run.

Each component records its grid-relative span, elongation, fill ratio and occupied-cell count. Small compact components are treated suspiciously, while long or connected components are left alone unless other evidence also argues against them.

## Local clutter evidence

Around each structural segment the Assistant samples ink density in eight surrounding directions. Marks with similarly high density in every direction are classified as isotropic/busy texture — a common signature of hatching, rubble, dense stair marks and decorative clusters rather than a clean wall with a quieter exterior side.

## Conservative rejection

Noise evidence primarily **demotes confidence**. A segment is rejected outright only when all of the following are true:

- it is topologically isolated;
- it belongs to a compact annotation-like component;
- its local neighbourhood is busy/isotropic; and
- its confidence remains low after the noise penalty.

Connected corners, junctions and continuations actively defend a segment against over-aggressive rejection. Texture evidence by itself can never delete a wall.

## Forensic metadata

Surviving wall suggestions carry `noiseEvidence`, `noisePenalty`, `noiseRejection` and the evidence model identifier `local-contrast-topology-noise-v4`. The Keeper review list surfaces the reason codes where relevant, so suspicious surviving strokes can be reviewed rather than silently hidden.

Doorway reasoning consumes the **noise-screened** structural wall set. This reduces the chance that furniture or annotation strokes create false wall continuity around a fake doorway.

## Keeper authority

As with the earlier forensic passes, this phase changes draft confidence only. The Keeper remains the final authority and no Scene geometry is written until selected suggestions are applied.

## Next

**IV.30.1G.4 — Multi-Resolution Forensic Rescan** can now spend extra pixels only on uncertain or high-value regions instead of analysing every large map at full resolution.
