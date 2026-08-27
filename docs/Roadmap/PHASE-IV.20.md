# Phase IV.20 — The Arsenal of the Adventurer

## Purpose

A combatant can now have several named attacks instead of one implicit Attack
profile.

Each Arsenal Attack carries its own kind, attack modifier, normal/long range,
damage dice, damage modifier, damage type, properties and source metadata.

The HUD exposes the active combatant's Arsenal. Changing the selected attack
immediately re-measures the current target, so a 5-foot weapon and a 60-foot
spell can produce different range verdicts without moving either token.

Both target preview and attack resolution send the selected attack ID back to
PHP. The server resolves that attack's CombatProfile and DamageProfile before
range, conditions, hit and damage calculations. Unknown attack IDs are denied.
Legacy single profiles remain as a compatibility fallback.

## Training Grounds

- Auby — Keeper's Staff / Keeper's Spark
- Training Slime — Slime Slam / Toxic Spit
- Frosty Cheese Thing — Chill Bite / Frost Shard
- Suspicious Training Dummy — Wooden Fist / Ember Pop

Preparing the existing Test Table refreshes these Arsenals.

## GMRC connection

Yes: IV.20 is deliberately the seam for the future GMRC character-sheet link.

The new `CompanionArsenalSource` contract accepts the opaque Companion character
reference already associated with a Tabletop token and returns a certified
`CombatArsenal`.

The intended direction is:

GMRC character sheet + equipped weapons/spell attacks
→ Companion combat projection
→ GMRT CombatArsenal
→ live Tabletop combat.

GMRC remains source of truth for the sheet. GMRT remains source of truth for the
live encounter. GMRT does not import the complete GMRC character domain and
does not create a hard PHP namespace dependency on the Companion plugin.

## Presentation fix

The battlemap viewport now clips its presentation layers, removing the stray
horizontal scrollbar observed during the IV.19.1 browser pass.
