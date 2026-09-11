# Phase IV.37.2 — That Was Not in Pippin's Notes

IV.37.1 gave the Keeper a live run sheet. IV.37.2 establishes the factual
boundary between what the Forge prepared and what actually happened at the
Table.

## Adventure facts

Significant Forge events are now written into the existing Chamber Chronicle:

- a dungeon secret is revealed;
- a trap is triggered manually by the Keeper;
- a trap is triggered by Player token movement;
- treasure is marked looted;
- a prepared story beat is resolved.

Each event uses `kind=adventure` plus a stable action and structured payload
containing identifiers such as Scene, trap, treasure, beat, or triggering token.

## Architectural rule

There is deliberately **no AdventureEventRepository** and no second history
store. `AdventureEventRecorder` is a narrow bridge into the existing
`ChamberChronicleRepository`.

The current Session recap builder already consumes Chamber Chronicle summaries,
so these facts become available to the established Session history without
inventing prose generation in this phase.

## Not yet

IV.37.2 does not attempt to write Pippin's final narrative, compare plan versus
reality, infer player intent, or automatically resolve story beats. Those later
features can now be built from structured facts rather than guesses.
