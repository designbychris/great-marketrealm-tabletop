# Changelog

## [0.16.0-alpha.1] - 2026-08-26

### Added

- Phase IV.16 — Under Strange Afflictions.
- Persistent blinded, charmed, frightened, grappled, poisoned, prone,
  restrained and stunned conditions.
- Optional turn-based condition duration and authoritative expiry.
- DM-only condition application and removal.
- Battle Events for condition application, removal and expiry.
- Condition state in Chamber refresh payloads.
- Token condition markers and DM Afflictions controls.
- SNES-style pixel bubbles for Poisoned with reduced-motion support.


## [0.15.0-alpha.2] - 2026-08-26

### Fixed

- Updated the vitality presentation regression to assert IV.15 authoritative
  resolved/raw damage output rather than the pre-IV.15 raw damage string.
- Updated the Test Table fixture regression for the four-part typed damage
  tuple: dice count, die size, modifier and damage type.


## [0.15.0-alpha.1] - 2026-08-26

### Added

- Phase IV.15 — The Wounds We Bear.
- Canonical damage types on Damage Profiles.
- Persistent per-token resistance, vulnerability and immunity.
- Deterministic defense resolution before Vitality damage.
- Raw/resolved damage and defense effects in Battle Events.
- RESIST, WEAK and IMMUNE browser combat announcements.
- IV.15 defense fixtures for Training Slime, Frosty Cheese Thing and
  Suspicious Training Dummy.
- Existing open test Tables can refresh their damage/defense fixture profiles.


## [0.14.2-alpha.1] - 2026-08-26

### Added

- Phase IV.14.2 — Pass the Turn.
- DM-only `End Turn` control in the live Encounter HUD.
- Automatic Chamber refresh after authoritative turn advancement.
- Human-readable current combatant labels in place of token UUIDs.


## [0.14.1-alpha.2] - 2026-08-26

### Fixed

- The empty Tabletop host now initializes `Prepare Test Table` before
  battlemap-only JavaScript checks.
- Prevented `tabletop.js` from exiting early when no Scene board exists yet.
- Added regression coverage for preparing the first test Table from an empty
  Chamber.


## [0.14.1-alpha.1] - 2026-08-26

### Added

- Phase IV.14.1 — The Steward's Test Table.
- One-click authenticated test Table bootstrap.
- Sage's Combat Testing Grounds and bundled battlemap fixture.
- Auby, Training Slime, Frosty Cheese Thing and Suspicious Training Dummy.
- Persisted combat profiles and automatically started test Encounter.


## [0.14.0-alpha.1] - 2026-08-26

### Added

- Phase IV.14 — Death at the Door.
- Death-save failures from damage received at 0 HP.
- Two death-save failures from critical damage at 0 HP.
- Stable-state interruption when a downed combatant takes damage.
- Excess-damage tracking for massive-damage resolution.
- Immediate Fallen state when qualifying massive damage reaches 0 HP.
- Shared vitality recovery service that clears death saves after healing.
- Death consequences embedded in `damage-applied` Battle Events.


## [0.13.0-alpha.1] - 2026-08-26

### Added

- Phase IV.13 — When Heroes Fall.
- Persistent per-token death-save state.
- Secure server-side death-save d20 resolution.
- Natural 1 double failures and natural 20 revival to 1 HP.
- Three-success stabilization and three-failure Fallen state.
- Authority checks for controlled Player tokens.
- Downed-combatant Battle Deed restrictions.
- Structured `death-save-resolved` Battle Events.
- Chamber death-save projection and first DOWN combat HUD.


## [0.12.0-alpha.1] - 2026-08-26

### Added

- Phase IV.12 — Blood on the Board.
- Persistent per-token Maximum, Current and Temporary HP.
- Temporary-HP-first damage application.
- Healing caps and non-stacking Temporary HP grants.
- Healthy, Wounded and Down vitality projection.
- Persistent damage profiles and secure damage dice.
- Critical hits double damage dice only.
- Successful attack damage application.
- Structured `damage-applied` Battle Events.
- First Tabletop party HP bars.


## [0.11.0-alpha.1] - 2026-08-26

### Added

- Phase IV.11 — The Clash of Arms.
- Server-side d20 attack resolution.
- Token Combat Profiles with Armor Class and attack modifier.
- Natural 20 critical hits and natural 1 critical misses.
- Target selection and same-Scene validation.
- Player protection against hidden attack targets.
- Structured `attack-resolved` Battle Events.
- Visible target selector and authoritative attack result announcement.


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
