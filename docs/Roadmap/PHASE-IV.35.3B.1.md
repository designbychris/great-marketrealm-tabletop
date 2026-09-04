# Phase IV.35.3B.1 — Pippin Refuses to Align the Chairs

A small Keeper-authoring polish pass for the Scene Object furniture tools.

- Furniture now exposes **Snap to Grid** inside Dungeon Master Controls.
- Snap is enabled by default.
- Both first placement and drag-to-move use the same snap helper.
- Turning Snap to Grid off preserves exact free-pointer coordinates.
- Rotation and scale remain independent of snapping.
- Snapping is an authoring preference only; Scene Objects persist only their final normalized coordinates.
- Existing Keeper-only authorization, exact Table + Scene identity, live heartbeat projection and Player read-only behaviour remain unchanged.

Pippin's field note: “The grid is there for a reason. Apparently the chair is not.”
