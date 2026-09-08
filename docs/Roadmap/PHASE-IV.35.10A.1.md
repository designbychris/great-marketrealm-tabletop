# Phase IV.35.10A.1 — The Attack Kind Actually Crosses the Bridge

The IV.35.10A regression suite correctly caught one remaining direct combat
vocabulary assertion inside `BestiaryCombatProvisioner::arsenalAttacks()`.

Damage types were already routed through `BestiaryCompatibilityNormalizer`,
but attack kinds for arsenal entries were still passed directly to
`AttackKind::assert()`. This hotfix routes those attack kinds through the same
Bestiary compatibility boundary, allowing neutral values such as
`Melee Weapon Attack` and `Ranged Weapon Attack` to be normalised consistently.

No catalogue or Boss Lair behaviour changes beyond correcting that missed seam.
