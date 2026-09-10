# Phase IV.37.1 — Pippin Turns the Page

## IV.37 — The Adventure at the Table

Phase IV.36 taught the Dungeon Forge to prepare a playable place. IV.37 begins
the next boundary: helping the Keeper run that prepared adventure during the
live session without turning narrative preparation into player-visible state.

### This pass

Pippin's Adventure Notes become a small Keeper-facing run sheet.

Each generated story beat can be:

- **Waiting** — prepared, but not currently in focus.
- **Current** — the single beat the Keeper is presently running.
- **Resolved** — completed during play.

The state is stored inside the existing Scene Forge projection. No new database
table or competing adventure domain is introduced. Existing IV.36.6 stories
without status metadata remain valid and are interpreted as Waiting.

Only one beat may be Current at a time. Resolved beats remain resolved when a
later beat becomes Current.

Player secrecy remains absolute: Forge story notes are still stripped at both
the rendered and AJAX presentation boundaries.

### IV.37 direction

Future IV.37 passes may connect Keeper-run beats to the Chamber Chronicle,
prepared rooms/events, selective player-facing discoveries, and eventual
Session recap composition. Those are deliberately outside IV.37.1.
