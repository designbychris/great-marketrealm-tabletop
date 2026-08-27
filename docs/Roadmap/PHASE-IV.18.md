# Phase IV.18 — The Measure of the Battlefield

## Purpose

Give the Tabletop an authoritative understanding of how far combatants are
from one another, then use that knowledge for attack legality, condition
rules, targeting presentation and Diceworks feedback.

## Battlefield distance

IV.18 supports square-grid measurement.

A Scene's persisted width, height and grid size convert each token's normalized
coordinates into grid space. Distance uses the closest occupied spaces of each
token footprint, so larger creatures do not behave like single points.

Square-grid diagonal movement follows the fifth-edition one-square diagonal
model. One grid step is five feet.

## Attack ranges

Combat Profiles now persist:

- normal attack range
- long attack range

Legacy profiles default to 5/5 feet, preserving a safe melee attack.

Attacks are measured before the Attack deed is spent.

- Within normal range: legal.
- Beyond normal but within long range: legal with disadvantage.
- Beyond long range: rejected as Out of Range.

This means clicking an illegal melee target cannot silently consume the
combatant's Action.

## Prone

IV.18 closes the Prone rule deliberately deferred in IV.17.

Attacks against a Prone target:

- gain advantage when the attacker is within 5 feet;
- suffer disadvantage when the attacker is farther than 5 feet.

These factors participate in the existing advantage/disadvantage cancellation
rule.

## Targeting preview

Selecting an attack target calls a read-only authenticated server endpoint.

The server returns:

- attacker and target IDs
- authoritative distance
- normal/long range
- IN RANGE / LONG RANGE / OUT OF RANGE state
- expected attack roll mode

The browser draws a temporary line between the current combatant and selected
target. The line is presentation only; its distance label comes from the
server calculation.

## Guild Diceworks

Combat now gains a Tabletop Diceworks presentation using the same interaction
language as the Great Marketrealm Companion Diceworks without creating a
runtime dependency between the two plugins.

The server still rolls every die.

Before the AJAX result returns, the Tabletop shows a stepped SNES-style rolling
state. Once the certified result arrives:

- a normal attack reveals one d20;
- Advantage reveals both d20s and highlights the higher selected result;
- Disadvantage reveals both d20s and highlights the lower selected result;
- the rejected die visibly dims/drops away;
- a natural 20 receives a victory pulse;
- a natural 1 deploys exactly one lonely pixel of confetti.

Reduced-motion preferences disable the animations.

The presentation never rerolls or chooses dice itself.

## Training Grounds

The fixture now contains both melee and ranged attacks:

- Auby: 5/5 ft
- Training Slime: 5/5 ft
- Frosty Cheese Thing: 30/60 ft
- Suspicious Training Dummy: 30/60 ft

Preparing the existing Test Table again refreshes these persisted Combat
Profiles without replacing the Table.

## Deferred

IV.18 deliberately leaves cover, line of sight, opportunity attacks, movement
budgets, area templates and hex-grid distance to later phases.
