# Phase IV.29C.1 — Eyes on the Enemy

Eyes on the Enemy gives each combat surface one clear responsibility. The Adventurer's Satchel owns Player actions, the Keeper's Bestiary owns deployed creature actions, and Turn of Battle reports the authoritative state of the fight and retains Keeper turn advancement.

## Combat ownership

- **Player:** when the current combatant is the viewer-controlled Companion character, the shared combat dock appears inside the Satchel.
- **Dungeon Master:** when the current combatant is a Bestiary creature, the dock appears on that exact deployed instance in the Bestiary. Multiple copies of one creature definition remain distinct battlefield combatants.
- **Turn of Battle:** no longer owns Attack/Dash/Disengage/Dodge/Help controls. It reports round/turn, target range and legality, and retains End Turn / Encounter lifecycle controls for the Keeper.

## Targeting

The target selector is built only from the viewer-safe Scene token projection. Selecting an eligible battlefield token also selects it in the combat dock. The browser submits token and attack identifiers only; existing server-authoritative combat services re-resolve membership, current turn, visibility, Scene, Arsenal, distance, AC, conditions, damage and defenses.

## Bestiary turn beacon

The current Bestiary instance receives an ACTIVE TURN marker and its definition card is highlighted. The Bestiary tab also gains a turn beacon, making it easy for the Keeper to find the acting monster without confusing the reusable definition with the Scene-owned instance.

## Phase boundary

This phase relocates and unifies existing combat interaction; it does not create a second combat engine and does not yet expand the catalogue. **IV.29D — The Keeper's Menagerie** remains the next Bestiary phase, followed by the Keeper's Cartography Assistant.

## Browser corrective — alpha.2

- Companion-certified Weapons to Hand now project into the active character token's Tabletop Combat Arsenal, so Player turns can select and resolve real battlefield attacks without browser-supplied combat maths.
- Denied creature attacks now halt Guild Diceworks explicitly instead of leaving the certified d20 in its rolling presentation state.
- Turn of Battle target feedback is presentation-only (`NO TARGET SELECTED`, range, legality) rather than resembling an inert Choose Target control.
- Player turns gain an explicit YOUR TURN badge in both the Satchel battle surface and shared Turn of Battle guidance, plus a Satchel tab turn beacon.
