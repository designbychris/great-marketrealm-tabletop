# Phase IV.35.2 — The Keeper's Furniture Palette

Phase IV.35.2 gives the Dungeon Master the first visible authoring surface for the Scene Object foundation introduced in IV.35.1.

The Keeper receives a compact Furniture Palette containing Table, Chair, Chest, Barrel, Crate and Bookshelf. Choosing a furnishing enters placement mode; the next click on the current projected battlefield converts that pointer position to normalised Scene coordinates and persists a new Scene Object against the exact Table + Scene identity. This works for the live Scene and for a Keeper-only Scene opened Behind the Curtain.

The six starter furnishings are deliberately simple CSS/SNES-style silhouettes. They prove that furniture is part of the persistent world layer rather than baked into a battlemap. Scene Objects render beneath miniatures and beneath the Living Veil, so unexplored furniture cannot leak information through Fog.

Live viewers reuse the existing Living Table heartbeat and authenticated Chamber fragment boundary to refresh the Scene Object layer. IV.35.2 creates no second polling loop.

## The Mimic Rule

Every registered furnishing is marked `mimic_capable`. This is intentionally universal: a future **Convert to Mimic** workflow must be available for any Scene Object, not only chests. That later workflow will let the Keeper choose an appropriate Mimic from the Bestiary while preserving the object's role as a disguise. A table, chair, rug, barrel or deeply suspicious bookshelf may therefore all become terrible decisions.

Rotation, scaling, repositioning, duplication, removal, collision/cover, interactive open/closed state and Bestiary-backed Mimic conversion remain later IV.35 phases.
