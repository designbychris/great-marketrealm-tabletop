# Phase IV.33.3 — Pippin Remembers the Way

The bare Tabletop route becomes the persistent campaign gateway for Keepers and Adventurers.

## Scope

- Present a full-bleed **Pippin's Table Atlas** using the established cartographer artwork.
- Discover Keeper-owned Tables and active/invited Player memberships from one permanent Tabletop link.
- Give active members a **Return to Table** route and invited Players a **Take My Seat** route.
- Keep campaign creation and Keeper-only player administration on the same gateway.
- Allow the owning Dungeon Master to permanently remove old, unused, or ended Tables after an explicit destructive confirmation.
- Purge the removed Table's table-keyed persistence records so it no longer remains discoverable.
- Keep Sage's Testing Grounds tucked under Development tools.

## Authority

Table removal is authenticated, protected by the existing Tabletop AJAX nonce, and authorized against the Table's persisted Dungeon Master owner. Players never receive the removal or player-management controls.

## Persistence principle

There is deliberately no manual Save button. The Living Table remains authoritative and persistent as play proceeds; this phase supplies the missing discovery and return journey.

## Final browser polish and legacy Table removal compatibility

- The Table Atlas gateway suppresses the in-Chamber masthead so Pippin's full scenic gateway begins directly beneath the site header.
- Permanent Table removal resolves early development/Testing Grounds records by their authoritative embedded Table UUID even if an older option storage key does not match that UUID.
- Removal remains Keeper-only and continues to purge table-keyed persistence after ownership is resolved.
