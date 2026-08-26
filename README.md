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

### Phase IV.3 — The Steward's Table Rules

The foundation is certified. IV.2 introduces the first real VTT domain:

- persistent Table identity
- Dungeon Master ownership
- **Preparing → Active → Ended** lifecycle
- Table repository abstraction
- application Table Registry
- configurable concurrent-table capacity
- initial **2 active Table** safety limit
- immediate capacity release when a session ends

Maps, tokens, player invitations, and networking remain deliberately outside this phase.

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
