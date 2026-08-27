# Phase IV.21 — The Cartographer's Table

## Purpose

The Dungeon Master can now replace the active Scene's battlemap artwork with
an image uploaded to or selected from the WordPress Media Library.

The map image is deliberately presentation data. The Scene identity, token
normalised coordinates and rules grid remain independent, which prepares the
Tabletop for IV.21A grid calibration and IV.22 Fog of War.

## Dungeon Master flow

1. Enter an active Table and Scene.
2. Choose **Choose Battlemap**.
3. Upload or select an image in the WordPress Media Library.
4. Choose **Use this Battlemap**.
5. GMRT validates the attachment server-side, records its Media attachment ID
   and native dimensions, then refreshes the board image in place.

Only an active Dungeon Master can perform the authoritative change. The AJAX
request uses the existing Tabletop nonce and the server independently verifies
that the selected attachment is an image.

## Architectural boundary

Battlemap artwork does not own token positions or the rules grid.

- Battlemap = visual world
- Grid = battlefield measurement
- Tokens = normalised 0..1 positions
- Fog/Vision = future visibility layer

That separation is intentional groundwork for the next phases.
