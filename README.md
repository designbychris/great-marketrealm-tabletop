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
