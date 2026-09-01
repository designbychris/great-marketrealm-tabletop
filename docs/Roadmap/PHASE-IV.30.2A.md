# Phase IV.30.2A — The Forge Creates Worlds

**Version:** `0.32.0-alpha.2`

The Cartographer's Dungeon Forge no longer needs an uploaded battlemap as its starting point. The Keeper can enter the Atlas, choose **Generate Dungeon**, name the place, choose a deterministic seed, scale, and visual stone/floor theme, and ask Pippin to create a complete new Scene.

## Atlas → Generate Dungeon

The Atlas workflow creates a new inactive Scene with a native generated surface rather than a WordPress Media attachment. The generated surface has explicit dimensions and a square gameplay grid, but `map_attachment_id` is deliberately zero and `surface_kind` is `generated`.

The browser still creates the deterministic geometry-first plan. The server validates that plan, creates the Scene, then passes the accepted topology through the same authoritative systems already certified by IV.30.2:

- connected dungeon floor artwork persisted through the Dungeon Forge repository;
- Vision Barrier walls and closed doors;
- the existing square gameplay grid;
- the existing Living Veil, enabled and reset for the new Scene;
- Keeper-owned environmental lights using the Lantern Rack radii.

If the authoritative build fails after Scene creation, Scene-owned state is cleared so the Keeper is not left with an empty or half-forged Atlas entry.

The new Scene does **not** become the live Player Scene automatically. After creation it opens **Behind the Curtain** for private Keeper inspection. The Keeper can then add thresholds, encounters, Bestiary creatures, adjust lights, or explicitly Open Scene when ready.

## Generated surface themes

Geometry and gameplay authority remain separate from presentation. A theme changes only the native SVG rock/floor treatment; it never changes wall, door, grid, Fog, token, or light coordinates.

The first Forge palette includes:

- Pantry Stone
- Butcher Cellar
- Rootland Cavern
- Frostreem Vault
- Bakery Crypt
- Mushroom Grotto

Both the existing in-Scene Forge and the Atlas world-creation workflow use the same theme vocabulary and the same deterministic generator.

## Compatibility

Existing image-backed Scenes continue to require a WordPress Media attachment. Old Scene records without `surface_kind` reconstitute as `image`, preserving the original contract. Replacing a generated Scene's artwork through the normal battlemap workflow turns it back into an image-backed Scene.

## Browser certification target

From the Keeper's Atlas:

1. Open **Generate Dungeon**.
2. Enter a new dungeon name.
3. Choose seed, scale, and Stone & Floor theme.
4. Select **Forge New Scene**.
5. Confirm the new Scene is added without choosing anything from Media Library.
6. Confirm it opens Behind the Curtain rather than replacing the live Player Scene.
7. Confirm generated artwork, grid, walls, closed doors, Fog, and lights are all present after refresh.
8. Forge the same seed/scale with a different theme and confirm topology remains deterministic while the artwork palette changes.
