# Phase IV.10 — Deeds in Battle

## Purpose

Give the current combatant a server-authoritative action economy and record
meaningful combat deeds as structured events.

## Turn resources

Every active turn begins with these unspent resources:

- Action
- Bonus Action
- Movement
- Reaction

IV.10 introduces the resource model even though the first canonical deeds all
spend the Action resource.

Advancing to the next combatant resets the turn economy.

## First Deeds

The initial canonical deed vocabulary is:

- Attack
- Dash
- Disengage
- Dodge
- Help

These are intentionally semantic actions, not full automated D&D resolution.

Attack does not yet roll to hit or deal damage.

## Authority

A deed may be performed only by:

- the active Dungeon Master; or
- the active Player controlling the current Character token whose opaque
  source reference matches their selected Companion Character.

The server decides who controls the turn.

## Events

Successful deeds append an immutable structured Battle Event containing:

- Table ID
- Encounter ID
- event type
- acting token ID
- round
- turn index
- timestamp
- deed payload

This event stream is deliberately presentation-neutral.

Future Pixel Auby, Pixel Sage and SNES combat HUD components may react to
events without contaminating combat rules with artwork logic.

## Concurrency

Deed requests include the expected Encounter revision.

Stale requests are rejected before resources are spent or events recorded.

## Visible controls

The Chamber exposes the first five deed buttons during an Active Encounter.

They are functional controls backed by the authenticated server endpoint.

## Not included yet

IV.10 does not yet implement:

- attack rolls
- damage or healing
- HP
- targets
- spell casting
- conditions
- reactions outside the resource model
- bonus-action-specific deeds
- movement allowance arithmetic
- full SNES combat HUD
