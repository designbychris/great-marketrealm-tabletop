# Phase IV.19 — The Chronicle of Battle

## Purpose

Separate immediate combat feedback from persistent encounter history.

The Guild Diceworks answers **what just happened**.
The Battle Chronicle answers **what has happened during this fight**.

## Immediate Diceworks result

After a certified attack resolves, the Diceworks now keeps the result next to
the visible dice rather than forcing the player to look below the battlemap.

The result card contains:

- HIT / MISS / CRITICAL HIT / CRITICAL MISS
- selected d20 + modifier + total vs Armor Class
- server-certified targeting distance when available
- resolved damage and damage type
- RESIST / WEAK / IMMUNE effects
- target Current / Maximum HP

The lower live-status region remains available for operational messages and
errors, but an ordinary attack now only reports that the attack resolved and
directs attention to Diceworks.

## Persistent Battle Chronicle

Battle Events were already persisted by the combat engine. IV.19 adds a
read-only projection layer which turns those domain events into compact,
human-readable Chronicle entries.

The Chronicle includes:

- attacks
- damage
- Dash / Dodge / Disengage / Help
- death saves
- condition application
- condition removal
- condition expiry

An Attack deed generates several domain events internally. The projector
suppresses the redundant `deed-performed: attack` entry and merges the
`attack-resolved` + matching `damage-applied` pair into one Chronicle line.

Example:

`Auby hit Training Slime — 2 SLASHING RESIST damage; 6/18 HP.`

## Privacy / visibility

The Chronicle is projected using only token labels already visible in the
viewer Chamber state.

If an actor token is hidden from a Player, events owned by that hidden token
are not projected into that Player's Chronicle. This prevents the history UI
from leaking hidden combatants.

## Ordering and size

The Chronicle displays the latest 12 projected entries, newest first.

The underlying persisted Battle Events remain the authoritative source. The
Chronicle itself does not create a second event store.

## Refresh

The normal Tabletop state endpoint now returns `battle_log`. The Chamber
refresh loop updates the Chronicle without rebuilding combat history in
JavaScript.
