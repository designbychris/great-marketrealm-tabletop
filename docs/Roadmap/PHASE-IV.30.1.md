# Phase IV.30.1 — Structural Cartography

Structural Cartography teaches the Keeper's Cartography Assistant to read a common hand-inked dungeon-map style where thick black architectural walls sit beside cross-hatched solid stone and do not necessarily coincide exactly with the calibrated grid.

The Structural tracing mode first builds a conservative dark-pixel mask from the already-loaded battlemap. It then searches for sustained horizontal and vertical dark boundaries whose immediate opposite sides are materially quieter. This suppresses repetitive hatch texture and isolated decoration while favouring the continuous ink boundaries that separate playable floor from solid dungeon structure.

Detected artwork traces are analysis geometry, not authoritative VTT geometry. The Assistant converts useful runs back into the existing calibrated grid barrier vocabulary, deduplicates them against saved Sight Beyond the Door barriers, and caps the private draft at 200 suggestions. Nothing is saved automatically: the Keeper still reviews, selects and explicitly applies suggestions through the existing IV.30 workflow.

This first structural pass deliberately improves the common orthogonal portions of irregular maps without pretending that every curve can be represented perfectly by the current grid-intersection barrier model. Curved and organic rooms can be approximated by short accepted segments where the trace crosses useful grid boundaries; future structural refinements may add richer free-angle tracing if browser testing demonstrates that it is warranted.

The benchmark for this phase is the retained cross-hatched dungeon map used during IV.30 browser testing: large circular chamber, thick inked walls, rectangular room blocks, irregular central passage and extensive stone hatching.
