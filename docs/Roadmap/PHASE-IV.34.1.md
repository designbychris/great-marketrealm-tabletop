# Phase IV.34.1 — The Keeper Calls the Session

Phase IV.34 begins **The Living Campaign**: a persistent Tabletop campaign may now contain many distinct play Sessions without making campaign persistence depend on a manual save button.

## Scope

- Add a persistent, Table-scoped Session domain with immutable Session number, title, start time, end time and status.
- Allow only the owning Dungeon Master to start or end Sessions.
- Starting without a title produces `Session N`; later Sessions continue numbering from the Table's history.
- An active Session survives refresh/re-entry and is projected through the existing Living Table heartbeat to every seated viewer.
- Display Session identity in the Table command masthead. Keepers can call/end the Session; Players see the authoritative live/between-sessions state.
- Ending a Session does **not** end or delete the campaign and does not introduce manual save semantics.
- Campaign deletion also removes its Session records.

## Deferred intentionally

Chronicle/encounter event tagging, recap generation, end-of-session encounter resolution, position snapshots, and next-session summaries belong to later IV.34 phases. IV.34.1 establishes only the authoritative lifecycle foundation.
