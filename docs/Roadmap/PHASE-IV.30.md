# Phase IV.30 — Keeper's Cartography Assistant

The Keeper's Cartography Assistant is a review-first aid for teaching the Living Veil about dungeon artwork without turning image analysis into an authoritative rules source.

The Assistant is available only to the Dungeon Master and operates on the Scene currently projected for the Keeper, including a privately prepared Behind-the-Curtain Scene. It samples the already-loaded battlemap artwork locally in the browser and compares likely dark/contrasting boundaries with the calibrated square grid. No battlemap pixels are sent to an external analysis service.

The analysis produces a private draft of room/wall boundary segments and conservative possible-door suggestions. Draft lines are visually separate from saved vision barriers. The Keeper can switch between Strong, Balanced and Fine detail, review each suggestion, select or deselect segments, clear the draft, or explicitly apply the selected set.

Nothing produced by the analysis becomes authoritative automatically. Applying a reviewed set submits only grid intersection geometry and barrier type to a DM-protected AJAX route. The server validates Table membership and Scene scope, caps a batch at 200 barriers, stores the accepted segments through the existing vision repository, and refreshes Fog exploration once after the batch. Once applied, suggested barriers become ordinary Sight Beyond the Door walls/doors and can be opened, removed, redrawn or undone with the established cartography tools.

The first Assistant pass intentionally requires a calibrated square grid. Gridless and non-square map interpretation remain future work rather than guessed geometry.
