# Phase IV.14.2 — Pass the Turn

Front-end screen testing exposed that the Encounter domain could advance turns,
but the Tabletop Chamber had no DM-facing control for doing so.

The active Encounter HUD now exposes **End Turn ▶** to the Dungeon Master.
It calls the existing nonce-protected `gmrt_advance_encounter` endpoint with
the Encounter's current optimistic revision.

On success the Chamber automatically reloads its Tabletop state. This rebuilds
the current-combatant label, target selector, deed controls, vitality/death-save
display, round number, and Encounter revision from authoritative persisted data.

The HUD now displays a combatant's readable token label (for example `Auby` or
`Training Slime`) rather than exposing the token UUID as the current turn.

The Encounter model already owns wraparound and round increments, so no turn
order rules are duplicated in JavaScript.
