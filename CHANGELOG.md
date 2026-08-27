# Changelog

## 0.22.1-alpha.4 — Phase IV.22A.2: The Veil Takes Hold

- Added true pointer-captured token dragging with live grab feedback while preserving server-validated movement on release.
- Kept token presentation above the visual fog layer; player token exposure remains server-filtered.
- Added server-projected character vision origins so the Living Veil stays anchored to the rendered battlefield under responsive/grid scaling.
- Added regression coverage for drag ownership, fog stacking, and rendered vision anchoring.

## [0.22.1-alpha.3] - 2026-08-27

### Fixed

- Phase IV.22A.1 — The Veil Remembers.
- Build current Fog of War visibility from the active Scene's canonical character tokens before viewer token-payload filtering, so DM Player Fog preview and player vision always receive the same authoritative sight sources.
- Keep hidden/non-player token payload filtering intact; sight projection no longer depends on which token records may be sent to the viewer.
- Added regression coverage proving Fog exploration survives unrelated encounter persistence and a fresh repository load, while explicit Reset Exploration remains the operation that clears memory.

## [0.22.1-alpha.2] - 2026-08-27

### Fixed

- Phase IV.22A.1 — The Anchored Veil.
- Anchored Fog of War calculations to the rendered browser width used when the DM saved grid calibration, fixing fully-black Player Fog caused by mixing display pixels with the battlemat's intrinsic image dimensions.
- Scaled Fog presentation to each viewer's rendered battlefield while preserving server-side native-map visibility calculations.
- Preserved Dungeon Master Player Fog preview across End Turn/page reloads using session-scoped presentation state.
- Added a visible one-time prompt to Save Grid when an older Scene has no Fog calibration anchor.
- Synchronized the death-save HUD from authoritative results so a natural 20 recovery to 1 HP immediately removes the stale DOWN panel; stable/deceased results also update in place.


## [0.22.1-alpha.1] - 2026-08-27

### Added

- Phase IV.22A — The Living Veil.
- Cohesive unexplored darkness instead of individually glowing fog tiles.
- Remembered terrain treatment that leaves previously explored map artwork dimly readable.
- Pixel-dithered vision and memory boundaries for a more SNES-like reveal edge.
- Reduced-motion support for Fog of War presentation transitions.


## [0.22.0-alpha.1] - 2026-08-27

### Added

- Phase IV.22 — The Veil of the Unknown.
- Persisted per-Scene Fog of War with unexplored and remembered territory.
- Three-square party vision around character tokens.
- Exploration automatically expands when character tokens move.
- Dungeon Master full-map bypass plus Player Fog preview mode.
- Player state suppresses non-character tokens outside current vision.
- Fog cells align to calibrated grid size and offsets.
- Dungeon Master reset-exploration control.

### Fixed

- Grid Save now reports progress/result beside the Cartographer controls, consumes the server-certified saved values, updates Reset Preview's baseline, and explicitly persists hidden-grid state.


## [0.21.2-alpha.2] - 2026-08-27

### Fixed

- Phase IV.21B.1 — The Steady Lens.
- Prevented native browser image dragging from interrupting battlefield panning.
- Reworked pan movement around pointer-down origin coordinates for stable, non-accumulating motion.
- Added full-gesture pointer capture and lost-capture cleanup.
- Added a four-pixel movement threshold to distinguish clicks from intentional camera pans.
- Preserved token interaction by excluding combatants and controls from camera pan gestures.


## [0.21.2-alpha.1] - 2026-08-27

### Added

- Phase IV.21B — The Cartographer's Lens.
- Battlefield zoom from 25% to 300%, Fit Map, Reset View and drag-to-pan.
- A single transformed battlefield coordinate-space so artwork, calibrated grid and tokens remain locked together.
- Presentation-only lens state that never mutates Scene, token, movement or grid calibration data.


## [0.21.1-alpha.1] - 2026-08-27

### Added

- Phase IV.21A — The Cartographer's Grid.
- Live DM grid scale, X/Y offset, opacity and visibility calibration.
- One-pixel directional grid nudging and resettable live preview.
- Persistent Scene grid calibration with backwards-compatible defaults.
- Server-authoritative active-DM grid saving with the Tabletop nonce.


## [0.21.0-alpha.1] - 2026-08-27

### Added

- Phase IV.21 — The Cartographer's Table.
- Dungeon Master battlemap selection through the WordPress Media Library.
- Server-side image validation and active-DM authorization.
- Persistent active Scene battlemap replacement using Media attachment IDs.
- Live board image refresh while preserving tokens and grid configuration.


## [0.20.0-alpha.2] - 2026-08-27

### Fixed

- Ensured the selected Arsenal attack ID is sent to the authoritative
  `gmrt_resolve_attack` request as well as the read-only targeting preview.
  This keeps range preview, attack modifier and damage profile on the same
  selected attack.
- Made the Chamber Arsenal projection regression insensitive to harmless
  fluent-call line wrapping.


## [0.20.0-alpha.1] - 2026-08-27

### Added

- Phase IV.20 — The Arsenal of the Adventurer.
- Persistent multi-attack Combat Arsenals.
- Named melee, ranged, natural, spell and improvised attacks.
- Per-attack range, modifier and damage resolution.
- Arsenal selector in the Turn of Battle HUD.
- Arsenal-aware targeting preview and Battle Event metadata.
- Future GMRC `CompanionArsenalSource` boundary using opaque character references.
- Two Training Grounds attacks for each test combatant.

### Fixed

- Removed the unintended horizontal battlemap scrollbar.


## [0.19.1-alpha.2] - 2026-08-27

### Fixed

- Corrected `TabletopDeathAtDoorRegressionTest` to read the actual Tabletop
  Chamber view at `app/Tabletop/Views/chamber.php`.


## [0.19.1-alpha.1] - 2026-08-27

### Fixed

- Scoped party-member list styles so Battle Chronicle entries no longer
  inherit the member card grid and overlap one another.
- Made Chronicle rows content-sized, wrapped and comfortably scrollable.

### Added

- Phase IV.19.1 — Chronicle & Fallen Combatant Presentation.
- Server-projected Healthy, Wounded, Downed, Defeated and Deceased states.
- DOWN / KO / DEAD token badges and future pixel-sprite CSS/data hooks.
- Distinction between 0 HP and confirmed death in board presentation.
- Live combatant-state refresh from authoritative Tabletop state.


## [0.19.0-alpha.1] - 2026-08-27

### Added

- Phase IV.19 — The Chronicle of Battle.
- Immediate Diceworks combat result card beside certified dice.
- Hit/miss arithmetic, damage defense effects and target HP in the HUD.
- Persistent Battle Event projection into a readable Battle Chronicle.
- Attack + Damage event merging and redundant Attack-deed suppression.
- Chronicle entries for deeds, death saves and condition lifecycle events.
- Viewer-safe Chronicle projection using visible token labels only.
- Live Chronicle refresh from Tabletop state.


## [0.18.0-alpha.2] - 2026-08-27

### Fixed

- Corrected footprint-aware battlefield distance so every occupied grid unit
  beyond a token's first square reduces the nearest-space gap by one full
  square.
- Updated the IV.17 affliction regression to assert IV.18's
  `attackRollFactors()` composition and `AttackRollMode::fromFactors()`
  resolution.


## [0.18.0-alpha.1] - 2026-08-27

### Added

- Phase IV.18 — The Measure of the Battlefield.
- Server-authoritative square-grid distance measured from token footprints.
- Persisted normal/long attack range on Combat Profiles.
- Server-side Out of Range rejection before the Attack deed is spent.
- Long-range attack disadvantage.
- Distance-sensitive Prone target rules.
- Authenticated read-only targeting preview.
- Live battlefield targeting line and range feedback.
- Tabletop Guild Diceworks tray with visible one/two-d20 rolls.
- Chosen/rejected Advantage and Disadvantage dice presentation.
- Natural 1 lonely pixel confetti and natural 20 victory feedback.
- Melee/ranged Training Grounds fixtures.


## [0.17.0-alpha.1] - 2026-08-26

### Added

- Phase IV.17 — Afflictions Take Hold.
- Server-authoritative attack advantage/disadvantage.
- Poisoned, Blinded, Prone and Restrained attacker penalties.
- Advantage against Blinded, Restrained and Stunned targets.
- Advantage/disadvantage cancellation.
- Stunned Battle Deed restriction.
- Grappled, Restrained and Stunned movement restrictions.
- Browser reporting of both d20s for condition-modified attacks.


## [0.16.0-alpha.2] - 2026-08-26

### Fixed

- Corrected the IV.16 condition repository tests to call the two-argument
  `ConditionRepository::save()` contract with a `TokenCondition`.
- Extended the Tabletop Chamber test harness with a condition repository
  double so the new IV.16 Chamber dependency is represented in unit tests.
- Added Chamber projection coverage for visible token conditions.


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
