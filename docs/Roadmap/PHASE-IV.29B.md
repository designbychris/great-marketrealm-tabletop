# Phase IV.29B — Summoned to the Table

The Keeper may now cross the deliberate boundary between a reusable Bestiary definition and a Scene-owned creature instance.

## Delivered

- Dungeon Master-only creature deployment.
- Deployment into the active Scene or the privately prepared Behind-the-Curtain Scene.
- Manual map-click placement using normalized authoritative Scene coordinates.
- Monster Deployment Threshold placement using the Scene's persisted threshold markers.
- Bounded multi-summon groups from 1 to 12 creatures.
- Grid-aware spreading around the chosen point or Threshold so groups do not all stack exactly on one coordinate.
- Distinct Scene-owned Creature token identities and stable `gmrt-bestiary:{creature-id}` source references.
- Optional Hidden from Players deployment using the existing server-authoritative token visibility boundary.
- Bestiary definitions remain immutable catalogue records; removing a battlefield token does not alter the Bestiary.
- Bestiary drawer tab receives additional vertical separation from the Keeper's Atlas tab.

## Explicitly deferred to IV.29C

- Automatically forging vitality, combat profiles, damage defenses and Combat Arsenal records from the Bestiary definition.
- Adding deployed creatures to an Encounter and initiative workflow directly from the Bestiary.
- Creature-specific condition/combat lifecycle integration beyond the existing generic Creature token rules.
