# Phase IV.17 — Afflictions Take Hold

## Purpose

Turn IV.16 conditions from persistent labels into rules the combat engine
actually obeys.

## Attack rolls

The attack resolver now supports:

- normal
- advantage
- disadvantage

Advantage rolls two d20s and keeps the higher result.
Disadvantage rolls two d20s and keeps the lower result.

If an attack has both advantage and disadvantage, they cancel and the attack
uses one normal d20.

The selected d20 still owns natural 1 / natural 20 resolution.

## Condition attack effects

The first authoritative effects are:

### Disadvantage on attacks made by

- Blinded
- Poisoned
- Prone
- Restrained

### Advantage on attacks against

- Blinded
- Restrained
- Stunned

Prone-target advantage/disadvantage is deliberately not implemented yet because
the tabletop does not yet have a certified melee-distance rule. The real rule
depends on whether the attacker is within 5 feet.

Frightened is also deferred because its attack penalty depends on whether the
source of fear is visible. Charmed requires source identity to enforce attack
restrictions correctly.

## Action restrictions

Stunned combatants cannot perform ordinary Battle Deeds.

The rule is enforced server-side in BattleDeedManager, so it applies to Attack,
Dash, Disengage, Dodge and Help rather than merely disabling a browser button.

## Movement restrictions

The movement service now rejects movement for:

- Grappled
- Restrained
- Stunned

Poisoned, Blinded and Prone remain movable in this phase.

## Presentation

When a condition changes an attack roll, the browser reports the mode and both
dice, for example:

`DISADVANTAGE [17 / 6] d20 6 + 5 = 11 vs AC 13`

or:

`ADVANTAGE [4 / 19] d20 19 + 3 = 22 vs AC 12`

The browser reports the server result and does not decide which die wins.

## Screen-test ideas

1. Poison Auby and attack → DISADVANTAGE with two d20s.
2. Stun Training Slime and try Attack / Dash → server should deny the deed.
3. Restrain Frosty Cheese Thing and try moving it → movement should be denied.
4. Attack a Stunned target → ADVANTAGE with two d20s.
5. Poison the attacker and Stun the target → effects cancel to one normal d20.
