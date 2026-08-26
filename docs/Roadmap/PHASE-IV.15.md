# Phase IV.15 — The Wounds We Bear

## Purpose

Give damage an authoritative type and allow tokens to defend against that type
through resistance, vulnerability and immunity.

## Damage types

The first certified vocabulary is:

acid, bludgeoning, cold, fire, force, lightning, necrotic, piercing, poison,
psychic, radiant, slashing and thunder.

A Damage Profile now owns its damage type. Legacy profiles without a stored
type resolve as slashing so existing Tables remain readable.

## Defense profiles

Each token may persist:

- resistances
- vulnerabilities
- immunities

Missing defense profiles are neutral.

## Resolution order

1. Roll raw damage.
2. Identify the attack's damage type.
3. If the target is immune, resolved damage becomes 0.
4. Otherwise resistance halves damage, rounding down.
5. Vulnerability then doubles the current value.
6. Vitality receives only the final resolved damage.

The order is deliberately deterministic. If a target is both resistant and
vulnerable to the same type, resistance is applied first and vulnerability
second. For odd raw damage this can differ by one point from the original raw
value because resistance rounds down.

Immunity takes precedence over both.

## Battle Events

`damage-applied` now includes a damage adjustment containing:

- damage type
- raw damage
- resolved damage
- defense effects

The browser consumes this data but does not calculate the result itself.

## Test Table

Sage's Combat Testing Grounds now exercises the system:

- Auby deals slashing damage.
- Training Slime resists slashing.
- Training Slime deals poison damage.
- Suspicious Training Dummy is immune to poison.
- Suspicious Training Dummy deals fire damage.
- Frosty Cheese Thing is vulnerable to fire.
- Frosty Cheese Thing deals cold damage.

Re-running `Prepare Test Table` from the empty `/tabletop/` host refreshes
damage and defense profiles on an existing open test Table, so the established
screen-testing fixture does not need to be destroyed.

## Browser expectations

Useful test sequence:

1. Auby attacks Training Slime → `RESIST!`
2. End Turn.
3. Training Slime attacks Suspicious Training Dummy → `IMMUNE!`
4. Advance through Frosty Cheese Thing.
5. Suspicious Training Dummy attacks Frosty Cheese Thing → `WEAK!`

## Deferred deliberately

IV.15 does not yet include conditions, mixed/multi-type damage packets,
resistance bypass, magical-weapon categories, or GMRC trait synchronisation.
