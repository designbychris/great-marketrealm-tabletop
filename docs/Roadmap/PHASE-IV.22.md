# Phase IV.22 — The Veil of the Unknown

Fog of War is a persisted Scene visibility layer independent from battlemat, grid, tokens and camera.

When enabled, unexplored grid cells are fully veiled. Cells explored previously remain visible only as dim memory, while cells currently within three grid squares of party character tokens are fully clear. Moving a character expands the Scene's exploration memory.

The Dungeon Master always receives a bypass view, but can enable **Preview Player Fog** to screen-test exactly how the veil is presented. Reset Exploration clears memory and reseeds currently visible character areas.

Player state does not merely cover unseen creatures with CSS: non-character tokens outside current party vision are withheld from the Player Chamber projection so Fog of War does not leak hidden combatants through the DOM/state payload.

This first Veil deliberately does not implement wall/door occlusion. The architecture leaves that as the next visibility refinement, so current vision is a square-radius reveal rather than X-ray-safe line of sight.
