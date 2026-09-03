# Phase IV.34.2 — The Table Remembers Tonight

Phase IV.34.2 joins the persistent Great MarketRealm Tabletop campaign to the Dungeon Master's canonical Companion Campaign and Session Ledger.

## Contract

- The Companion remains authoritative for Campaigns, Session Ledger records, and Fellowship links.
- The Tabletop remains authoritative for the actual played Session lifecycle and its start/end timestamps.
- The plugins communicate through explicit WordPress filter contracts rather than reaching into one another's classes or storage.
- A Keeper links a Tabletop campaign to one of their active Companion Campaigns from Pippin's Table Atlas.
- Linkage is stored against the Companion Campaign by immutable campaign/table IDs, not names.
- Linking backfills existing Tabletop Sessions into the Companion Session Ledger.
- Starting and ending subsequent Tabletop Sessions synchronises the same Ledger record using the immutable Tabletop Session ID.
- An existing unlinked Companion Session with the same number may be adopted, preserving DM prep notes, recap and attendance.
- Start/end timestamps and duration are persisted as certified Tabletop metadata; a live Session appears as `In Progress`, and an ended Session becomes `Played`.
- The linked Fellowship identity is projected back to the Table Atlas, preparing the safe public Chronicle bridge for IV.34.3.

## Deliberate boundary

IV.34.2 does not yet publish a Session entry into the Fellowship Company Chronicle. That shared/public projection is IV.34.3 so DM-private Session Ledger material cannot leak into the Fellowship record.
