# Great Marketrealm Tabletop

> **The live virtual tabletop for adventures across The Great Marketrealm.**

Great Marketrealm Tabletop (**GMRT**) is a dedicated WordPress virtual tabletop built to work alongside **Great Marketrealm Companion (GMRC)**.

The Tabletop owns live game-table state. The Companion remains the canonical home of Characters, Fellowships, Bestiary creatures, spells, equipment, and other certified Marketrealm records.

## Golden Integration Rule

> **Tabletop may depend on and integrate with Companion. Companion must never depend on Tabletop.**

Cross-plugin communication must happen through explicit contracts in:

`app/Integration/Companion/`

Gameplay code must not reach directly into arbitrary Companion internals.

## Current Milestone

### Phase IV.15 — The Wounds We Bear

Damage now carries an authoritative type and targets can persist resistance,
vulnerability and immunity. The server resolves those defenses before Vitality
changes and records both raw and final damage in Battle Events.

The Training Grounds now provides browser fixtures for RESIST, WEAK and IMMUNE.

### Phase IV.14.2 — Pass the Turn

Dungeon Masters can now advance the active Encounter directly from the Chamber.
The HUD shows the current combatant by name and refreshes automatically after
each turn so targeting and deed controls follow the initiative order.

### Phase IV.14.1 — The Steward's Test Table

The empty Tabletop host can now prepare `Sage's Combat Testing Grounds` with a
real Scene, Auby, Training Slime, Frosty Cheese Thing, Suspicious Training
Dummy, combat profiles and an active Encounter for front-end screen testing.

### Phase IV.14 — Death at the Door

Damage now has authoritative consequences after a combatant reaches 0 HP.
Further hits cause death-save failures, critical hits cause two failures,
damage breaks Stable state, and massive excess damage can immediately mark a
combatant Fallen.

Healing above 0 HP clears the current death-save sequence through the shared
recovery service.

### Phase IV.13 — When Heroes Fall

Combatants at 0 HP now enter an authoritative death-save flow. Three successes
stabilize, three failures mark the combatant Fallen, natural 1 counts as two
failures, and natural 20 restores 1 HP.

Downed combatants cannot perform ordinary Battle Deeds, and the Chamber gains
its first death-save HUD.

### Phase IV.12 — Blood on the Board

Successful attacks now roll and apply server-authoritative damage. Tokens gain
persistent Maximum HP, Current HP and Temporary HP, with Healthy / Wounded /
Down presentation states and the Tabletop's first real HP bars.

Critical hits double damage dice rather than flat modifiers. Death saves and
Companion HP synchronisation remain future work.

### Phase IV.11 — The Clash of Arms

Attack Deeds can now target another token and resolve a server-side d20 attack
against Armor Class. Natural 20s are critical hits, natural 1s are critical
misses, and every result is recorded as a structured Battle Event.

Damage and HP are intentionally not applied yet.

### Phase IV.10 — Deeds in Battle

Active Encounters now have a server-authoritative turn economy and first
canonical Deeds: Attack, Dash, Disengage, Dodge and Help.

Successful deeds spend their required turn resource and append a structured
battle event for future logs, combat HUDs and Pixel Auby/Sage reactions.

### Phase IV.10A — Tabletop Page Host Integration

The visible Tabletop now follows the Companion-style WordPress Page host
pattern. Create a normal `/tabletop/` Page and place:

`[great_marketrealm_tabletop]`

Elementor/theme code owns the site shell while GMRT owns the Chamber and all
authoritative VTT behaviour.

### Phase IV.9 — The Turn of Battle

The Tabletop now has a persistent server-authoritative Encounter engine:

- Scene-bound Encounters
- Preparing, Active, Paused and Ended lifecycle
- token-based initiative rosters
- deterministic initiative tie breaking
- round and turn progression
- Dungeon Master-only controls
- revision conflict protection
- Chamber battle-state projection

The full SNES-era combat HUD and Pixel Auby/Sage reactions remain future
presentation work on top of this engine.

### Phase IV.8 — The Living Table

The Chamber is now interactive while PHP remains authoritative:

- selectable tokens
- DM movement of active-Scene tokens
- Player movement of their assigned Character token only
- keyboard and click movement
- token revision conflict protection
- authenticated state refresh every five seconds
- no WebSocket dependency yet

### Phase IV.7 — The Tabletop Chamber

The VTT now owns its first visible front-end at **`/tabletop/`**.

The chamber renders the active Table Scene, square grid, persisted tokens and
the gathering of Table members. Dungeon Masters can see Hidden preparation
tokens while Players receive Visible tokens only.

The first chamber is deliberately read-only. Interactive movement arrives in
the next certified phase.

### Phase IV.6 — Tokens on the Table

IV.6 places persistent game pieces onto Table Scenes:

- Character, Creature and Object tokens
- opaque GMRC/Bestiary source references
- optional controlling WordPress users
- normalised Scene positioning
- token footprint sizing
- Visible/Hidden preparation state
- persistence across Scene switches

The visible VTT shell is reserved for **IV.7 — The Tabletop Chamber** at **`/tabletop/`**.

### Phase IV.5 — The First Battlemap

IV.5 gives each Table persistent battlemap Scenes:

- multiple preserved Scenes per Table
- WordPress Media attachment-backed maps
- square-grid and gridless foundations
- one active Scene at a time
- safe Scene switching
- normalised token-ready coordinates

Tokens, fog of war and real-time networking remain deliberately outside this phase.

### Phase IV.4 — The Gathering of Adventurers

IV.4 gives Tables their first persistent adventuring party:

- Dungeon Master and Player Table roles
- invitation-required player joining
- reconnectable Table membership records
- safe player leaving and re-invitation
- Table-level management and participation permissions
- optional opaque references to Companion-owned Characters
- automatic Dungeon Master seating when a Table is prepared

Character data itself remains owned by Great Marketrealm Companion. GMRT stores only the Character reference selected for that Table membership.

Maps, tokens, and real-time networking remain deliberately outside this phase.

## Technical Identity

| Item | Value |
| --- | --- |
| Plugin | Great Marketrealm Tabletop |
| Short name | GMRT |
| Slug | `great-marketrealm-tabletop` |
| PHP namespace | `GreatMarketrealmTabletop` |
| Initial version | `0.1.0-alpha.1` |
| Minimum PHP | 8.1 |
| Development phase | IV |

## Development

```bash
composer install
php vendor/bin/phpunit --display-warnings
```

See `ROADMAP.md` and the `docs/` directory for the architecture plan.

**First we build the table. Then we roll initiative.** 🎲

### Phase IV.19.1 — Chronicle & Fallen Combatant Presentation

Battle Chronicle entries now wrap and size correctly instead of inheriting the
party-member card grid. Token presentation also distinguishes Healthy, Wounded,
Downed, Defeated and Deceased without treating 0 HP as automatic death.

### Phase IV.19 — The Chronicle of Battle

Immediate attack results now remain beside the Guild Diceworks, including roll
maths, damage defenses and target HP. Persisted Battle Events are projected into
a compact Battle Chronicle showing the latest encounter deeds without
duplicating Attack and Damage events.

### Phase IV.18 — The Measure of the Battlefield

The Tabletop now measures authoritative square-grid distance between token
footprints, validates melee/ranged attack ranges before spending an Action,
handles long-range disadvantage and completes the distance-sensitive Prone
rule.

Target selection draws a visible battlefield line with server-certified range
feedback, while the first Tabletop Guild Diceworks tray visibly rolls one or
two d20s and reveals the already-certified server result.

### Phase IV.17 — Afflictions Take Hold

Conditions now influence authoritative combat. Poisoned, Blinded, Prone and
Restrained can impose attack disadvantage; Blinded, Restrained and Stunned
targets can grant advantage; Stunned blocks Battle Deeds; and Grappled,
Restrained or Stunned combatants cannot move.

Advantage and disadvantage are resolved server-side with two d20s and cancel
each other when both apply.



Current development milestone: **0.32.0-alpha.7 — Phase IV.30.2A.1A, The Mystery of the Corner Tile — forensic pass III**.


### Phase IV.30.2A — The Forge Creates Worlds

The Keeper's Atlas can now create a complete generated dungeon Scene without selecting a background image. A deterministic seed and scale produce the same geometry-first dungeon foundation used by IV.30.2, while a separate Stone & Floor theme repaints the native SVG surface. The new Scene receives its square grid, vision walls, closed doors, Fog and Keeper lights immediately, then opens Behind the Curtain for private inspection before the Keeper chooses to make it live.


### Phase IV.30.2A.1 — Pippin Decorates the Place
Dungeon Forge themes now paint deterministic procedural detail directly into generated SVG surfaces. Pantry Stone, Butcher Cellar, Rootland Cavern, Frostreem Vault, Bakery Crypt and Mushroom Grotto each receive their own floor/rock language without changing the authoritative generated topology.
