# Changelog

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
