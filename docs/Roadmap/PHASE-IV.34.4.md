# Phase IV.34.4 — Deeds of the Session

The Living Campaign now gives the things that happen at the Table a stable Session identity.

## Delivered

- Encounters prepared or begun during an active Session persist that Session's immutable ID.
- Battle Chronicle events automatically inherit the active Session ID at persistence time.
- Chamber Chronicle events automatically inherit the active Session ID at persistence time.
- Existing event constructors remain backward compatible: historic records outside a Session remain valid with an empty Session ID.
- Battle and Chamber repositories can retrieve records for a specific Session, establishing the source material for later recaps.
- Live Chronicle projections preserve the Session ID without changing the current player-facing Chronicle presentation.
- Keeper secret rolls remain excluded because they never enter Chronicle persistence.

## Boundary

This phase records provenance; it does not yet generate narrative recaps or publish individual private/secret activity to the Fellowship Company Chronicle. IV.34.5 may consume these Session-scoped deeds to build the Keeper-editable “Previously, in the MarketRealm…” recap.
