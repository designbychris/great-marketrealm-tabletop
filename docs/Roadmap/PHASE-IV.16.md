# Phase IV.16 — Under Strange Afflictions

## Purpose

Introduce a server-authoritative condition lifecycle for combatants.

## Conditions

The first vocabulary is blinded, charmed, frightened, grappled, poisoned,
prone, restrained and stunned.

A token may carry several different conditions at once. Re-applying the same
condition replaces its duration rather than creating duplicates.

## Duration

A condition may be indefinite or carry a number of turns remaining.

Timed conditions count down when the affected combatant's own turn ends.
A one-turn condition therefore remains visible throughout that combatant's
turn and expires as the Dungeon Master passes that turn.

Expiry is authoritative and recorded as a Battle Event.

## Authority

Only the active Dungeon Master may apply or remove conditions in IV.16.
Players can see conditions but cannot alter them.

## Battle Events

The condition engine records:

- condition-applied
- condition-removed
- condition-expired

## Chamber

The Encounter HUD now includes a compact DM-only Afflictions control for
choosing a combatant, condition and optional duration.

Tokens expose compact condition markers. Poisoned receives the first playful
SNES-era presentation treatment: tiny stepped pixel bubbles. Reduced-motion
preferences disable the animation.

## Deliberately deferred

IV.16 establishes condition state and lifecycle. Mechanical consequences such
as disadvantage, movement restrictions and incapacitation are intentionally
deferred so each rule can be added and tested explicitly rather than hidden
inside presentation code.
