# Phase IV.12 — Blood on the Board

## Purpose

Make successful attacks matter by introducing server-owned vitality and damage.

## Vitality

Each Table token may have persistent:

- Maximum HP
- Current HP
- Temporary HP

Tokens without a richer imported profile use a safe temporary default of
10 Maximum HP / 10 Current HP / 0 Temporary HP.

## Damage order

Incoming damage is applied in this order:

1. Temporary HP absorbs as much as possible.
2. Remaining damage reduces Current HP.
3. Current HP never falls below 0.

## Healing

Healing never increases Current HP above Maximum HP.

Temporary HP is not healing and is managed separately.

## Temporary HP

A new Temporary HP grant replaces the existing grant only when the new value
is higher. Temporary HP does not stack.

## Vitality states

- Healthy: Current HP equals Maximum HP.
- Wounded: Current HP is above 0 but below Maximum HP.
- Down: Current HP is 0.

`Down` is deliberately presentation state only in IV.12. Death saves,
unconsciousness and character-death mechanics are not implemented yet.

## Damage profiles

Each attacker may have a Damage Profile:

- dice count
- die size
- modifier

Until richer character/monster integration supplies the value, tokens default
to **1d6 damage**.

Supported damage dice are d4, d6, d8, d10, d12 and d20.

## Critical damage

A critical hit doubles the number of damage dice.

It does **not** double the flat damage modifier.

For example:

`2d6 + 4` becomes `4d6 + 4`.

## Attack integration

A successful IV.11 attack now:

1. resolves hit / miss / critical result;
2. rolls damage when the result is a hit;
3. applies damage to the target's vitality;
4. persists the updated vitality;
5. appends a `damage-applied` Battle Event.

Misses do not roll or apply damage.

## Presentation

The Gathering sidebar gains the first real HP bars where a Table member can
be matched to their selected Character token.

Attack announcements now include rolled damage and remaining HP.

The later SNES combat HUD may turn the same vitality projection into richer
party bars, damage numbers and Pixel Auby/Sage reactions without changing
combat rules.

## Not included yet

IV.12 does not implement:

- death saving throws
- unconsciousness restrictions
- instant-death rules
- damage resistances or immunities
- healing deeds/spells
- damage types
- concentration
- conditions
- GMRC Character Ledger HP synchronisation
