# Phase IV.14 — Death at the Door

## Purpose

Close the dangerous edge cases around 0 HP so the Tabletop has a coherent
server-owned dying and recovery lifecycle.

## Damage at 0 HP

When a combatant already at 0 HP takes damage from a successful attack:

- a normal hit causes one failed death save;
- a critical hit causes two failed death saves.

This does not use the massive-damage rule because the combatant was already
at 0 HP before the attack.

If the combatant had been Stable, taking damage clears Stable and resumes the
death-save sequence.

## Massive damage

Massive damage applies only when a combatant begins above 0 HP and the attack
reduces them to 0 HP.

After Temporary HP is absorbed and Current HP reaches zero, the engine records
the remaining excess damage.

If that excess damage is at least the token's Maximum HP, the combatant is
immediately marked Fallen.

Example:

- Maximum HP: 10
- Current HP: 4
- Incoming damage: 15
- 4 damage reduces Current HP to 0
- 11 damage remains
- 11 >= Maximum HP 10
- Result: Fallen by massive damage

Temporary HP reduces the amount that can become excess damage.

## Healing and recovery

Healing that restores Current HP above 0 clears the active death-save sequence:

- successes return to 0
- failures return to 0
- Stable is cleared

This is exposed through `VitalityRecoveryManager` so future healing Deeds,
spells and Companion integration use one authoritative recovery path.

Zero healing does not clear a downed combatant's death-save state.

## Events

The existing `damage-applied` event now also records:

- death consequence
- updated death-save state

Supported death consequences in this phase include:

- `none`
- `damage-failure`
- `critical-damage-failures`
- `massive-damage`

## Deferred deliberately

IV.14 does not yet implement automatic melee criticals against unconscious
targets because GMRT does not yet have a certified distance/condition rule
for that interaction.

Resurrection and permanent-character-death policy also remain future work.
