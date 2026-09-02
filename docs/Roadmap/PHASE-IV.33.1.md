# Phase IV.33.1 — The Keeper Names the Campaign

The Testing Grounds remain a development fixture, but they are no longer the only way into the Tabletop. A signed-in Dungeon Master can now create and reopen persistent campaign Tables from the Tabletop threshold.

## Scope

- Add a Keeper Campaign Shelf when no `table` is selected.
- List non-ended Tables owned by the current Dungeon Master.
- Add **Create Tabletop** with a required campaign name and optional description.
- Persist description alongside existing Table metadata without breaking old records.
- Seat the creator as the authoritative Dungeon Master through the existing Gathering service.
- Activate the new Table and create an empty generated Scene named **The First Blank Page**.
- Redirect directly into the new Table after creation.
- Keep **Sage's Combat Testing Grounds** available under a deliberately secondary Development tools disclosure.
- Protect creation with the existing Tabletop AJAX nonce and a WordPress/Companion-friendly Dungeon Master policy filter.

## Authority boundary

WordPress remains identity authority. Table membership remains gameplay authority after creation. `gmrt_tabletop_may_create_table` is the explicit integration seam for Companion role policy; recognised DM/admin roles work directly in the standalone plugin.

## Deferred

Rename/archive, richer campaign art, duplication/templates, Fellowship import and first-map choices remain later IV.33 phases.

And, for the avoidance of doubt, Tables may have any number of legs — including zero.
