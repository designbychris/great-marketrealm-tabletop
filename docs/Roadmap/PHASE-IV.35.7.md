# Phase IV.35.7 — Forge Learns Interior Design

Pippin's Dungeon Forge now performs a deterministic furnishing pass after
architecture has been accepted.

## Architecture first

The Forge continues to generate floor, walls, doors and room rectangles first.
Furniture is never baked into the generated SVG. `ForgeFurniturePlanner`
classifies usable room rectangles and proposes items from the existing
`FurnitureCatalogue`.

## Placement rules

- tiny connector spaces and corridor-like rectangles stay empty;
- furniture remains inside the room that owns it;
- a two-square approach is reserved around generated doors;
- object footprints cannot overlap;
- total furnishing density is bounded;
- forests are intentionally sparse until the wider MarketRealm furnishing
  catalogue adds better outdoor props.

## Real Scene Objects

Each accepted proposal is persisted through the existing
`SceneObjectRepository`, carrying the exact same movement, cover, vision,
light-occlusion, interaction and future Mimic properties as Keeper-placed
furniture. Chests begin closed.

The projection records deterministic furnishing metadata for audit/debugging,
while the actual playable furniture remains in the Scene Object layer.

## Cleanup

Forge-created Scene Objects now participate in Scene rollback/deletion through
`SceneShelfCleaner`, including its flat `gmrt_scene_objects` persistence shape.

> Pippin: “I have placed the chair where the door is not.”
>
> Keeper: “Excellent.”
>
> Pippin: “The standards for cartography have changed considerably.”
