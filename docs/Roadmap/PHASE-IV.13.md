# Phase IV.13 — When Heroes Fall

## Purpose

Give the IV.12 `Down` state real combat consequences.

## Death saves

A combatant at 0 HP may make an authoritative server-side d20 death save.

- 10–20: one success
- 2–9: one failure
- Natural 1: two failures
- Natural 20: regain 1 HP and leave the Down state
- Three successes: Stable
- Three failures: Fallen

Death-save state persists per Table token.

## Authority

Only an active Table member may request a death save. A Player may roll only
for the token they control. The Dungeon Master retains Table authority.

A combatant above 0 HP cannot make a death save.

## Downed combatants

A token at 0 HP cannot perform ordinary Battle Deeds. Death saves use their
own authoritative flow rather than consuming ordinary deed resources.

## Natural 20

A natural 20 restores the combatant to exactly 1 HP and clears the current
death-save sequence, moving vitality from `Down` back to `Wounded`.

## Stabilisation and Fallen state

Three successful saves mark the combatant Stable without restoring HP.
Three failed saves mark the combatant Fallen.

Permanent character death and resurrection policy remain future work.

## Events

Every roll appends a `death-save-resolved` Battle Event containing the d20
result, accumulated saves, current vitality, round and turn context.

## Presentation

The combat HUD can now show:

`DOWN · Saves 2/3 · Failures 1/3`

and expose `Roll Death Save` when appropriate.

## Deferred deliberately

IV.13 does not yet implement damage-at-zero failures, critical melee damage at
0 HP, instant death / massive damage, resurrection, or GMRC Character Ledger
synchronisation.
