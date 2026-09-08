# Phase IV.35.10A — The Lair Checks the Entire Bestiary

The Boss Lair browser test exposed two older integration assumptions.

First, neutral Companion creature records were mapped against a narrow field
shape. Records using common aliases such as `armorClass`, `hitPoints`, `slug`,
`label`, or nested `stats`/`combat` measures could be silently omitted from the
Keeper's Menagerie. The external mapper now accepts those neutral variants
without weakening the requirement that a deployable creature has a stable ID,
name, Armor Class, and Hit Points.

Second, Tabletop's combat layer expected its exact internal damage and attack
kind strings. A creature carrying presentation-friendly values such as
`Piercing Damage`, `Melee Weapon Attack`, or common aliases now crosses an
explicit Bestiary compatibility normalizer. Unknown values still fail, but the
error names the actual unsupported value instead of the opaque
`Unsupported damage type.` message.

The Companion adapter now also recognises two optional additive neutral-record
contracts:

- `gmrc_tabletop_bestiary_supplemental_records`
- `gmrc_tabletop_bestiary_workshop_records`

These are merged with the established published
`gmrc_tabletop_bestiary_records` shelf by stable creature identity, allowing
Companion to expose additional encounter-ready Workshop records without
Tabletop importing Companion repositories or classes.

This phase does not invent statistics for incomplete Workshop drafts. If a
record still lacks valid AC or HP it remains non-deployable until its canonical
Companion data is completed.

> Pippin: “Administration has found some of the monsters. This is not the
> reassuring outcome they think it is.”
