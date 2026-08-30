# Phase IV.29D — The Keeper's Menagerie

The Keeper's Bestiary becomes a source-agnostic Menagerie. Tabletop keeps its training shelf and consumes optional external creature records through `BestiarySource`; the first adapter is Great Marketrealm Companion.

## Boundary

- Tabletop never imports Companion PHP classes or repositories.
- Companion publishes neutral creature snapshots through `gmrc_tabletop_bestiary_records`.
- Only encounter-ready published records are exported.
- Existing deployed tokens remain instance-owned snapshots; catalogue refreshes affect only future summons.
- If Companion is absent, the Training Grounds shelf remains fully usable.
- The Bestiary catalogue remains DM-only.

## Next

Keeper's Cartography Assistant.
