# Phase IV.36.4B — The Keeper Opens the Trap Cabinet

The trap lifecycle becomes a proper Dungeon Master authoring tool instead of a
set of marker-only controls.

- Adds **The Keeper's Trap Cabinet** inside Dungeon Master Controls.
- Lists every Forge trap on the current Scene with its label, type, armed state,
  triggered state and discovery state.
- Adds manual Pressure Plate and Tripwire placement: choose a trap and click the
  battlemap.
- Existing traps can be renamed, have their type changed, moved, revealed,
  concealed, armed/disarmed, deliberately sprung, reset or removed.
- `Re-arm` and `Reset & Conceal` are now separate operations. Re-arm preserves a
  discovered trap; Reset & Conceal restores the original hidden/armed/unsprung
  preparation state.
- Fixes the marker-control seam that could omit `scene_id` on the active Scene.
  That meant Reveal/Reset could ask the server to update a trap without telling
  it which Forge projection contained the trap.
- Marker actions now refresh the Chamber through the normal fragment boundary
  instead of relying on a blind browser reload.
- The same explicit scene-id correction is applied to secret reveals.
- Player secrecy remains unchanged: unrevealed traps still do not cross the
  Player presentation or live-state boundary.

Expected suite: **1,158 tests**.
