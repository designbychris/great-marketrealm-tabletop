# Phase IV.35 — Pippin Furnishes the Dungeon

Phase IV.35 begins the Tabletop's persistent **Scene Object** system. Furniture is not painted into a battlemap or generated SVG: it is a first-class Scene-owned layer that can later be positioned, transformed, interacted with, hidden by the Living Veil, and integrated with collision, cover, lighting and Dungeon Forge workflows.

## IV.35.1 — The Object Layer

This foundation introduces:

- a Scene-scoped `SceneObject` domain model with stable identity;
- normalised placement coordinates independent of battlemap dimensions;
- rotation and scale transform seams for the forthcoming Keeper placement tools;
- `state` for later open/closed, lit/doused and similar interactive states;
- `properties` for later collision, vision, cover, loot and other object behaviours;
- Decorative, Structural and Interactive categories;
- an explicit repository contract and WordPress persistence adapter using `gmrt_scene_objects`;
- scene-level clearing so future Atlas Scene deletion can remove Scene-owned objects without touching other Scenes.

No furniture palette or browser placement controls are introduced in IV.35.1. Those belong to the next furnishing pass; this phase establishes the architecture they will consume.

> Pippin's first inventory request contained seventeen chests and no chairs. His explanation remains: “Chairs have never tried to eat me.”
