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

### Phase IV.1 — The Empty Table

The first milestone establishes the independent plugin foundation:

- WordPress plugin bootstrap
- self-contained PSR-4 autoloading
- application lifecycle
- activation/deactivation boundaries
- Companion availability contract
- PHPUnit scaffolding
- architecture regression tests
- initial server-capacity seed of **2 concurrently active tables**

No maps, tokens, or real-time gameplay are part of IV.1 yet.

First, we build the table.

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
