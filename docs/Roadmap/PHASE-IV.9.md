# Phase IV.9 — The Turn of Battle

## Purpose

Give the Great Marketrealm Tabletop a persistent, server-authoritative
Encounter and initiative engine.

## Encounter ownership

An Encounter belongs to one Table and one Scene.

Combatants reference existing GMRT token IDs only. The Encounter engine does
not own Characters, creatures, positions, HP or artwork.

## Lifecycle

The Encounter lifecycle is:

**Preparing → Active → Paused → Ended**

Preparing allows the Dungeon Master to assemble initiative.

Active enables round and turn advancement.

Paused preserves the current combatant and round without allowing normal turn
advancement.

Ended is terminal historical state.

## Initiative

Combatants store:

- token ID
- initiative result
- initiative modifier

Ordering is deterministic:

1. higher initiative
2. higher initiative modifier
3. token ID ascending

The final tie breaker ensures every client receives the same order.

## Turns and rounds

Starting an Encounter establishes Round 1 and the first combatant.

Advancing beyond the final combatant resets the turn index and increments the
round.

The server owns this arithmetic.

## Authority

Only an Active Dungeon Master may prepare or control an Encounter.

Encounter control endpoints derive identity from the current WordPress user;
the browser does not supply a Dungeon Master identity.

## Revisions

Encounter state uses monotonically increasing revision numbers.

Stale control requests receive conflict semantics rather than overwriting
newer Encounter state.

## Chamber projection

The Tabletop Chamber receives the current Encounter for the active Scene and
renders a deliberately modest battle strip:

- Encounter name
- lifecycle status
- round
- current token ID

This is a semantic hook for the future SNES-era combat HUD.

## Pixel characters

Pixel Auby and Pixel Sage are deliberately not coupled to the Encounter
engine. Future presentation layers may react to Encounter events and state
without placing art concerns inside battle rules.

## Not included yet

IV.9 does not implement:

- HP/damage
- conditions
- actions/spells
- automated dice initiative
- player turn completion
- reaction timing
- targeting
- full combat HUD
- Pixel Auby/Sage animations
- WebSocket synchronisation
