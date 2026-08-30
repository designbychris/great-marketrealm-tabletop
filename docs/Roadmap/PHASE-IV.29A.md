# Phase IV.29A — The Keeper's Bestiary

## Purpose

Give the Dungeon Master a private creature register without coupling reusable
creature definitions to battlefield token instances.

The Keeper's Bestiary is a second right-hand DM drawer, following the successful
Keeper's Atlas interaction pattern. It is searchable and inspectable, but does
not deploy creatures yet. Deployment is deliberately reserved for IV.29B —
Summoned to the Table.

## Definition boundary

A Bestiary Creature Definition is not a TableToken.

One Training Slime definition may later produce many independently identified,
Scene-owned Slime instances. Removing an instance will never delete the Bestiary
entry. The Bestiary catalogue is also never projected to Players.

The first shelf mirrors the already combat-certified Training Grounds rather
than inventing new mechanics:

- Training Slime — AC 11, HP 18; Slime Slam / Toxic Spit; resists slashing.
- Frosty Cheese Thing — AC 12, HP 22; Chill Bite / Frost Shard; weak to fire.
- Suspicious Training Dummy — AC 13, HP 30; Wooden Fist / Ember Pop; immune to poison.

The model already has slots for ability scores, saving throws, senses and traits
so richer canonical Marketrealm creature records can be added without changing
the domain boundary.

## Privacy

Only the Dungeon Master receives the Bestiary projection. Player chamber state
contains an empty Bestiary array, matching the Atlas/Threshold privacy rule.

## Presentation

The drawer provides:

- searchable name/kind/action/damage/defence text;
- compact AC, HP and Speed measures;
- expandable actions, defences and traits;
- an intentionally disabled `Summon to Scene · IV.29B` control that makes the
  phase boundary explicit rather than pretending deployment already exists.

## Next

**IV.29B — Summoned to the Table** will turn a chosen definition into a unique,
Scene-owned creature instance through manual placement or Monster Deployment
Thresholds, including Behind-the-Curtain preparation.
