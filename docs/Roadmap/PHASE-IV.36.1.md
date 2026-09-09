# Phase IV.36.1 — The Boss Notices the Adventurers

Phase IV.35 taught Dungeon Forge to construct and furnish a Boss Lair, place a
real Bestiary creature in it, and keep that occupant hidden from Players during
exploration. IV.36.1 crosses the next semantic boundary: **the prepared boss may
become an ordinary Encounter combatant when the Keeper starts battle**.

## Contract

The Forge remains responsible only for preparation metadata. It records the
canonical Bestiary creature and the exact Scene token created for the lair.
The Encounter system remains the sole owner of rounds, turns and combatants.
There is deliberately no `BossEncounter`, boss-only initiative list, or second
combat state.

`LairEncounterParticipant` is the narrow bridge between those systems. When a
fresh Encounter is beginning it checks the current Scene's Forge projection. A
lair occupant is awakened only when:

- the Forge still identifies both the Bestiary creature and its exact token;
- that exact token is among the combatants chosen by the Keeper;
- the token still belongs to the Encounter Scene; and
- its source reference still matches the expected Bestiary creature.

If those conditions hold, a hidden exploration occupant is revealed to Players
and the existing `EncounterManager::begin()` path proceeds normally. If the
Keeper leaves the boss unchecked, it remains hidden and dormant.

## Keeper presentation

The existing **Start Encounter** panel now identifies the forged `Boss Lair
occupant` in the combatant register and explains the reveal boundary. This is
not a separate boss-start button: the Keeper retains the same encounter name,
combatant selection and initiative controls used for every battle.

## Regression seal

The phase adds focused coverage for:

1. revealing a selected forged boss at the encounter boundary;
2. leaving an unselected boss hidden during exploration;
3. refusing stale Forge metadata that points at an unrelated Bestiary token;
4. Scenes without a lair occupant remaining entirely ordinary; and
5. the architecture continuing to route the feature through the existing
   Encounter manager and Start Encounter presentation.

> **Pippin:** “It noticed us.”
>
> **Keeper:** “Roll initiative.”
>
> **Pippin:** “I preferred it when the furniture was the problem.”
