# Phase IV.30.1B — The Living Contour

The Living Contour complements IV.30.1A rather than replacing Structural Cartography. Constructed dungeons continue to use the directional Structural tracer; cave and organic maps gain a dedicated floor/solid-boundary strategy.

Living Contour uses the calibrated square grid as a scale reference. It samples the interior of each map cell, classifies quiet white/gridded playable floor against darker hatched or solid rock, rejects isolated decorative classifications, then traces every shared floor/solid boundary into a private wall draft. Conservative corner simplification replaces eligible one-square stair-step L pairs with diagonals so irregular cave boundaries produce a more useful line-of-sight approximation without bridging intentional openings.

The benchmark contract is deliberately practical rather than pixel-perfect: the control dungeon should remain strongest under Structural tracing, while the advanced cave benchmark should produce continuous playable cave boundaries under Living Contour. The hostile misaligned-grid benchmark remains reserved for later grid-registration work.

Nothing is saved automatically. Living Contour suggestions use the same Keeper-only review, deduplication, 200-segment cap and explicit Apply Selected route as the existing Cartography Assistant.
