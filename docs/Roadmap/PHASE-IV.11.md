# Phase IV.11 — The Clash of Arms

## Purpose

Turn the existing Attack Deed into the Tabletop's first resolved combat action.

IV.11 deliberately resolves **whether an attack hits** without applying damage.
This keeps attack authority, dice resolution and targeting independently
certifiable before HP enters the engine.

## Combat profiles

Each token may have a server-owned Combat Profile containing:

- Armor Class
- attack modifier

Until richer Companion/monster integration supplies a profile, tokens use a
safe neutral default of **AC 10** and **attack +0**. This means the first attack
flow can be screen-tested immediately without inventing client-side mechanics.

## Attack flow

1. The current combatant chooses Attack.
2. A target token on the same Scene is selected.
3. Existing Deeds in Battle authority verifies the acting member.
4. Attack spends the Action resource.
5. The server rolls a d20 using `random_int(1, 20)`.
6. The attack modifier is added.
7. The total is compared with the target's Armor Class.
8. The result is appended to the Battle Event stream.

## Critical results

- Natural 20: `critical-hit`, regardless of Armor Class.
- Natural 1: `critical-miss`, regardless of modifier.
- Otherwise total >= AC: `hit`.
- Otherwise: `miss`.

No damage is applied in IV.11.

## Target safety

An attacker cannot target itself with this first Attack implementation.
Targets must share the same Scene.

Players cannot attack a token hidden from them. Dungeon Masters retain
authority over hidden tokens.

## Events

An `attack-resolved` event records:

- attacker token
- target token
- d20 roll
- attack modifier
- total
- target Armor Class
- result
- hit boolean
- round and turn context

This is presentation-neutral so the future SNES combat HUD, combat log,
Pixel Auby and Pixel Sage can react to the same authoritative event.

## Presentation

The active combat strip gains a target selector. Attack now calls a dedicated
resolution endpoint and announces the authoritative result.

The natural-20 celebration and the critically important lonely natural-1
pixel of confetti remain presentation work: IV.11 creates the event data they
will consume.
