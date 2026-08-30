# Phase IV.29C — Creatures in Battle

A creature summoned from the Keeper's Bestiary now arrives as a complete Tabletop combatant rather than an unconfigured token.

## Delivered

- Every newly summoned Bestiary creature receives an instance-owned combat snapshot at deployment time.
- Bestiary Armour Class becomes the token's authoritative Combat Profile Armour Class.
- Bestiary Hit Points become authoritative Maximum and Current Vitality.
- Every Bestiary attack becomes a Combat Arsenal attack with its own attack modifier, normal/long range, damage dice, damage modifier, damage type and properties.
- The first Bestiary attack also seeds the legacy Combat Profile and Damage Profile compatibility path.
- Bestiary resistances, weaknesses and immunities become the existing Damage Defense Profile used by authoritative damage resolution.
- Summoned creatures therefore use the existing initiative selection, turn order, targeting, range measurement, attack resolution, vitality, conditions, defeated-state and Battle Chronicle systems instead of creating parallel Bestiary combat rules.
- Hidden creature filtering remains server-authoritative: a Player who cannot receive the creature token also receives none of its vitality, conditions or Arsenal projection.
- Combat state is a snapshot owned by the battlefield instance. Later Bestiary catalogue edits do not silently mutate a creature already deployed to a Scene.

## Boundary preserved

The Bestiary definition still knows nothing about Scenes, tokens or combat persistence. `BestiaryCombatProvisioner` is the explicit adapter from a catalogue definition to the existing Tabletop combat repositories.

## Next

**IV.29D — The Keeper's Menagerie** will introduce an external/custom Bestiary source boundary so the catalogue can grow beyond the bundled Training Grounds shelf, including Companion-backed canonical Great Marketrealm creature records where available.
