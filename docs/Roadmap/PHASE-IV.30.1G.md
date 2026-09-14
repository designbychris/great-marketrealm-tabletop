# Phase IV.30.1G — The Cartographer Looks Again
## Adaptive Evidence Model

Imported battlemaps disagree profoundly about what a wall looks like in raw pixel values. A scanned parchment dungeon may draw architecture in soft grey-brown ink, a digital crypt may use near-black walls, and a cave illustration may place dark playable floor beside only slightly darker rock. Structural Cartography therefore cannot treat one absolute luminance threshold as the definition of wall ink.

IV.30.1G adds an **adaptive evidence layer** underneath the existing Structural reader. The browser now builds summed-area tables for luminance and squared luminance once per Cartography Assistant pass. This gives Pippin constant-time local mean and local deviation measurements throughout the imported artwork without introducing a server-side image-analysis dependency.

For each candidate ink block the reader compares the sample against a larger local neighbourhood. A mark can qualify because it is meaningfully darker than the floor immediately around it even when its absolute luminance would have been too pale for the historic threshold. Conversely, globally dark artwork no longer becomes wall evidence merely because the whole map sits below an arbitrary darkness value. The old `92` threshold remains only as a conservative fallback for genuinely dark ink that also differs from its surroundings.

The directional Structural scorer then carries that adaptive evidence into the existing centre/side-density test. Strong local contrast can rescue pale architectural ink, but it does **not** bypass the existing quiet-side and cross-trace checks used to reject hatch, handwriting-like flecks and busy stone texture. In other words: **local contrast is evidence, not authority**.

Every resulting barrier remains a private Structural/Hybrid draft. Existing review limits, duplicate suppression and the Keeper's explicit selection/apply step are unchanged. **Nothing is saved automatically.**

### Certification intent

This phase is the foundation for the later forensic passes rather than an attempt to solve doors, furniture, junctions or high-resolution rescanning all at once. It should improve tolerance across light/dark map styles while preserving today's conservative failure mode. Follow-up phases can layer corner/junction reasoning, doorway interpretation, annotation rejection and selective high-resolution rescans on the same evidence model.
