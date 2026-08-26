# Changelog

## [0.10.0-alpha.2] - 2026-08-26

### Added

- Phase IV.10 — Deeds in Battle.
- Action, Bonus Action, Movement and Reaction turn-resource model.
- Attack, Dash, Disengage, Dodge and Help deed vocabulary.
- Server-authoritative current-combatant deed permissions.
- Automatic turn-resource reset during turn advancement.
- Persistent structured Battle Event log.
- Authenticated nonce-protected battle-deed endpoint.
- First visible Deed controls in the Tabletop Chamber.


## [0.10.0-alpha.1] - 2026-08-26

### Changed

- Phase IV.10A — Tabletop Page Host Integration.
- Replaced direct `/tabletop/` rewrite interception with the Companion-style
  `[great_marketrealm_tabletop]` shortcode.
- WordPress/Elementor now owns the Tabletop Page header, footer and layout.
- Chamber rendering is now a content fragment suitable for shortcode hosting.
- Table selection supports shortcode attributes and `?table=` navigation.
- Existing movement, state and Encounter AJAX endpoints remain unchanged.
- Removed the Tabletop rewrite-flush activation dependency.


## [0.9.0-alpha.1] - 2026-08-26

### Added

- Phase IV.9 — The Turn of Battle.
- Persistent Scene-bound Encounter records.
- Preparing, Active, Paused and Ended Encounter lifecycle.
- Token-based combatants with deterministic initiative ordering.
- Round and current-turn progression.
- Dungeon Master-only Encounter control policy.
- Encounter revision conflict protection.
- Authenticated nonce-protected Encounter AJAX endpoints.
- Current Encounter projection into Tabletop Chamber state.
- Minimal battle strip ready for the future combat HUD.


## [0.8.0-alpha.1] - 2026-08-26

### Added

- Phase IV.8 — The Living Table.
- Server-authoritative Tabletop movement policy.
- Dungeon Master movement of active-Scene tokens.
- Player movement limited to their matching assigned Character token.
- Token revision numbers and stale-update conflict protection.
- Authenticated nonce-protected AJAX movement and state endpoints.
- Five-second authoritative Table state refresh.
- Keyboard and click token movement controls.
- Selected-token and live-status presentation states.
- No WebSocket dependency; transport remains intentionally simple.


## [0.7.0-alpha.1] - 2026-08-26

### Added

- Phase IV.7 — The Tabletop Chamber.
- GMRT-owned `/tabletop/` and `/tabletop/{table-id}/` front-end routes.
- Logged-in and active-Table-membership access boundary.
- Server-assembled Tabletop Chamber view state.
- Active battlemap Scene and square-grid rendering.
- Persistent token rendering using normalised Scene coordinates.
- Dungeon Master visibility of Hidden preparation tokens.
- Player filtering of Hidden tokens.
- Table gathering sidebar, empty states and unavailable-map handling.
- Responsive Tabletop styling with reduced-motion support.
- Read-only initial chamber ahead of interactive movement.


## [0.6.0-alpha.1] - 2026-08-26

### Added

- Phase IV.6 — Tokens on the Table.
- Persistent Character, Creature and Object token records.
- Opaque external source references without direct Companion coupling.
- Optional token controller WordPress user IDs.
- Normalised Scene placement and movement.
- Width/height token footprint foundations.
- Visible and Hidden token preparation state.
- WordPress token persistence adapter and application manager.
- Ended-Table token preservation and mutation guards.
- `/tabletop/` reserved for the IV.7 visible VTT shell.


## [0.5.0-alpha.1] - 2026-08-26

### Added

- Phase IV.5 — The First Battlemap.
- Persistent Table Scene records and WordPress storage adapter.
- WordPress Media attachment references for battlemap artwork.
- Square-grid and gridless Scene foundations.
- One active Scene per Table with non-destructive switching.
- Normalised token-ready coordinates independent of rendered map pixels.
- Ended-Table Scene preservation and mutation guards.
- Scene domain, persistence, factory and architecture regression coverage.


## [0.4.0-alpha.1] - 2026-08-26

### Added

- Phase IV.4 — The Gathering of Adventurers.
- Persistent Table membership records.
- Dungeon Master and Player Table roles.
- Invited, Active and Left player membership lifecycle.
- Automatic Dungeon Master seating for newly prepared Tables.
- Invitation, join, leave and re-invite application service.
- Table-level management and participation permissions.
- Optional opaque Companion Character references without duplicating Companion data.
- WordPress membership persistence adapter and regression coverage.


## [0.3.0-alpha.1] - 2026-08-26

### Added

- Phase IV.3 — The Steward's Table Rules.
- Renewable active-Table leases and persisted heartbeat timestamps.
- Heartbeat grace windows and automatic expired-session reclamation.
- Capacity reclamation before new activation checks.
- Steward capacity override identities for controlled testing.
- Operational policy regression coverage.


## [0.2.0-alpha.1] - 2026-08-25

### Added

- Phase IV.2 — The First Table.
- Persistent Table domain identity and Dungeon Master ownership.
- Preparing, Active and Ended Table lifecycle.
- Table repository contract and WordPress persistence adapter.
- Table Registry application service.
- Configurable concurrent active-table capacity policy.
- Initial two-active-table protection and immediate slot release on end.
- Table-domain and capacity regression coverage.


## [0.1.0-alpha.1] - 2026-08-25

### Added

- Phase IV.1 — The Empty Table foundation.
- Independent WordPress plugin bootstrap.
- Self-contained `GreatMarketrealmTabletop` PSR-4 autoloader.
- Application lifecycle and `gmrt()` helper.
- Activation/deactivation boundaries.
- Initial schema version.
- Initial active-table capacity seed of 2.
- Stable Companion availability contract.
- PHPUnit configuration and first regression suite.
- Architecture and roadmap documentation.
