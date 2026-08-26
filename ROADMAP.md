# Great Marketrealm Tabletop Roadmap

## Phase IV — The Great Marketrealm Tabletop

### Phase IV.1 — The Empty Table

- [x] Plugin identity and bootstrap
- [x] Self-contained PSR-4 source autoloader
- [x] Application root and boot lifecycle
- [x] Activation/deactivation boundaries
- [x] Companion integration boundary
- [x] Initial concurrent-table capacity seed
- [x] PHPUnit scaffold
- [x] Architecture regression tests
- [ ] Server-side certification on the deployment environment

### Phase IV.2 — The First Table

- [x] Stable Table identity
- [x] Dungeon Master ownership
- [x] Preparing → Active → Ended lifecycle
- [x] Persistent repository contract
- [x] Initial WordPress persistence adapter
- [x] Configurable active-table capacity policy
- [x] Initial limit of 2 simultaneously active tables
- [x] Capacity released immediately when a Table ends
- [x] Application-level Table Registry
- [x] Domain and regression coverage
- [ ] Server-side certification on the deployment environment

### Phase IV.3 — The Steward's Table Rules

- [x] Configurable active-table capacity
- [x] Renewable active Table leases
- [x] Heartbeat-based lease renewal
- [x] Heartbeat grace window
- [x] Automatic expired-session reclamation
- [x] Capacity reclaimed before activation
- [x] Steward capacity overrides
- [x] Safe minimum lease/grace settings
- [x] Server-load regression coverage
- [ ] Server-side certification on the deployment environment

### Phase IV.4 — The Gathering of Adventurers

- [x] Persistent Table membership records
- [x] Dungeon Master and Player Table roles
- [x] Invited → Active → Left player lifecycle
- [x] Dungeon Master automatically seated for prepared Tables
- [x] Invitation-required player joining
- [x] Safe leave/re-invite behaviour
- [x] Table-level management/participation permissions
- [x] Opaque Companion Character references without duplicated Character data
- [x] Ended Tables reject new gathering changes
- [x] Membership persistence and regression coverage
- [ ] Server-side certification on the deployment environment

### Phase IV.5 — The First Battlemap

- [x] Persistent Table Scene records
- [x] WordPress Media attachment references for battlemap artwork
- [x] Battlemap pixel dimensions
- [x] Square-grid and gridless scene foundations
- [x] Configurable grid size
- [x] Exactly one active Scene per Table
- [x] Scene switching without destructive loss
- [x] Normalised token-ready coordinates
- [x] Ended Tables preserve scenes but reject scene changes
- [x] Scene persistence and regression coverage
- [ ] Server-side certification on the deployment environment

### Phase IV.6 — Tokens on the Table

- [x] Persistent token identity
- [x] Character, Creature and Object token vocabulary
- [x] Opaque Companion/Bestiary source references
- [x] Optional WordPress user controller identity
- [x] Scene-bound token placement
- [x] Normalised X/Y movement
- [x] Width/height footprint foundations
- [x] Visible/Hidden token state
- [x] Token persistence across Scene switching
- [x] Ended Tables preserve tokens but reject token mutation
- [x] Token domain and persistence regression coverage
- [ ] Server-side certification on the deployment environment

### Phase IV.7 — The Tabletop Chamber

- [x] GMRT-owned `/tabletop/` front-end route
- [x] Optional `/tabletop/{table-id}/` direct Table route
- [x] Logged-in Table membership gate
- [x] Active Scene battlemap rendering
- [x] Square-grid visual overlay
- [x] Persistent token rendering from IV.6 records
- [x] Hidden tokens visible to Dungeon Masters only
- [x] Table gathering/member sidebar
- [x] Empty, unavailable-map and access-denied states
- [x] Responsive shell and reduced-motion treatment
- [x] Read-only first chamber with no premature drag transport
- [ ] Server-side certification on the deployment environment

### Phase IV.8 — The Living Table

- [x] Keyboard/click token selection
- [x] Dungeon Master token movement
- [x] Player movement of matching assigned Character token only
- [x] Server-authoritative movement policy
- [x] Authenticated nonce-protected AJAX movement endpoint
- [x] Incremental authoritative Table state refresh
- [x] Token revision numbers for stale-update protection
- [x] HTTP 409 conflict semantics for stale token movement
- [x] Active-Scene-only movement
- [x] Read-only hidden-token filtering remains server-side
- [x] No WebSocket dependency
- [ ] Server-side certification on the deployment environment

### Phase IV.9 — The Turn of Battle

- [x] Scene-bound persistent Encounter records
- [x] Preparing → Active → Paused → Ended lifecycle
- [x] Token-based combatant roster
- [x] Deterministic initiative ordering
- [x] Initiative modifier and token-ID tie breakers
- [x] Round and current-turn progression
- [x] Dungeon Master-only Encounter control
- [x] Encounter revision conflict protection
- [x] Authenticated nonce-protected Encounter endpoints
- [x] Active-Scene lifecycle protection
- [x] Ended Tables retire current Encounter state
- [x] Chamber battle-state projection for future combat HUD
- [ ] Server-side certification on the deployment environment

### Phase IV.10A — Tabletop Page Host Integration

- [x] Real WordPress/Elementor Page owns `/tabletop/`
- [x] Companion-style `[great_marketrealm_tabletop]` shortcode
- [x] GMRT renders Chamber content as a page fragment
- [x] Theme/Elementor owns header, footer and surrounding layout
- [x] Existing Tabletop AJAX and Encounter endpoints preserved
- [x] Table selection supported by shortcode attribute or `?table=` query
- [x] Direct `/tabletop/` rewrite interception retired
- [x] Rewrite-flush activation dependency removed
- [ ] Server-side certification on the deployment environment

### Phase IV.10 — The Shared Table

Lower-latency shared-state synchronisation, presence and reconnection foundations.


### Phase IV.10 — Deeds in Battle

- [x] Canonical Action / Bonus Action / Movement / Reaction resources
- [x] First canonical Deeds: Attack, Dash, Disengage, Dodge, Help
- [x] Server-owned turn-resource expenditure
- [x] Automatic turn-resource reset on turn advancement
- [x] Current-combatant authority checks
- [x] Dungeon Master or controlling Player deed permissions
- [x] Encounter revision conflict protection
- [x] Structured persistent battle-event log
- [x] Authenticated nonce-protected Deed endpoint
- [x] First visible Chamber Deed controls
- [ ] Server-side certification on the deployment environment


### Phase IV.11 — The Clash of Arms

- [x] Server-side d20 attack roller
- [x] Per-token combat profiles with AC and attack modifier
- [x] Safe default profile: AC 10 / attack +0
- [x] Attack target selection
- [x] Natural 20 critical-hit rule
- [x] Natural 1 critical-miss rule
- [x] Modified attack total versus Armor Class
- [x] Attack spends the existing Action resource
- [x] Structured attack-resolved Battle Event
- [x] Hidden-target protection for Players
- [x] Encounter revision conflict protection
- [x] First visible target selector and attack result announcement
- [ ] Damage and HP application (future phase)
- [ ] Pixel critical animations (presentation phase)
- [ ] Server-side certification on deployment


### Phase IV.12 — Blood on the Board

- [x] Server-owned Maximum / Current / Temporary HP
- [x] Temporary HP absorbs damage before Current HP
- [x] Healing capped at Maximum HP
- [x] Healthy / Wounded / Down vitality states
- [x] Per-token persistent vitality
- [x] Per-token damage profiles
- [x] Secure damage dice rolling
- [x] Critical hits double damage dice, not modifiers
- [x] Successful attacks apply authoritative damage
- [x] Structured `damage-applied` Battle Events
- [x] Chamber vitality projection
- [x] First party HP bars
- [ ] Death saves and unconsciousness rules
- [ ] GMRC HP synchronisation
- [ ] Server-side certification on deployment


### Phase IV.13 — When Heroes Fall

- [x] Persistent per-token death-save state
- [x] Server-side secure d20 death saves
- [x] 10+ success / 9- failure
- [x] Natural 1 counts as two failures
- [x] Natural 20 restores the combatant to 1 HP
- [x] Three successes stabilize
- [x] Three failures mark the combatant Fallen
- [x] Only downed combatants may roll death saves
- [x] Player authority restricted to controlled token
- [x] Downed combatants blocked from ordinary Battle Deeds
- [x] Structured `death-save-resolved` Battle Events
- [x] Chamber death-save projection
- [x] First DOWN / Saves / Failures combat HUD
- [ ] Damage-at-zero death-save failures
- [ ] Instant-death / massive-damage rules
- [ ] GMRC character-state synchronisation
- [ ] Server-side certification on deployment


### Phase IV.14 — Death at the Door

- [x] Damage at 0 HP causes death-save failures
- [x] Critical damage at 0 HP causes two failures
- [x] Damage breaks Stable state
- [x] Three damage failures produce Fallen state
- [x] Excess damage is tracked after Temp HP and Current HP
- [x] Massive damage can cause immediate Fallen state
- [x] Massive-damage calculation only applies when dropping from above 0 HP
- [x] Healing above 0 HP clears death-save successes/failures
- [x] Damage events include death consequence and death-save state
- [x] Attack endpoint returns updated death-save projection
- [ ] Melee-distance auto-critical rules for unconscious targets
- [ ] Resurrection policy
- [ ] GMRC character-state synchronisation
- [ ] Server-side certification on deployment

### Phase IV.14.1 — The Steward's Test Table

- [x] One-click authenticated test Table bootstrap
- [x] Sage's Combat Testing Grounds
- [x] Real Table, Scene, Token and Encounter services
- [x] Bundled neutral battlemap imported into WordPress Media
- [x] Auby controlled by the preparing user
- [x] Training Slime
- [x] Frosty Cheese Thing
- [x] Suspicious Training Dummy
- [x] Persisted HP, AC, attack and damage profiles
- [x] Active test Encounter
- [x] Idempotent reuse of an existing open test Table
- [x] Redirect directly into the prepared Table
- [ ] IV.15 damage-type defenses added to fixture creatures

### Phase IV.14.2 — Pass the Turn

- [x] DM-only End Turn control in the active Encounter HUD
- [x] Existing authoritative Encounter advance service reused
- [x] Optimistic Encounter revision sent with the request
- [x] Automatic full HUD refresh after a successful turn advance
- [x] Round wrapping remains owned by the Encounter domain model
- [x] Current turn displays the combatant label instead of an opaque token UUID
- [x] Target list is rebuilt for the newly active combatant after turn advance


### Phase IV.15 — The Wounds We Bear

- [x] Canonical damage-type vocabulary
- [x] Damage Profiles carry one authoritative damage type
- [x] Per-token resistance, vulnerability and immunity profiles
- [x] Immunity resolves to zero damage
- [x] Resistance halves damage and rounds down
- [x] Vulnerability doubles damage
- [x] Deterministic resistance-then-vulnerability ordering
- [x] Defense resolution occurs before Vitality damage
- [x] Battle Events record raw and resolved damage plus defense effects
- [x] Attack endpoint exposes authoritative damage adjustment
- [x] Browser announces RESIST / WEAK / IMMUNE
- [x] Existing Test Tables can refresh IV.15 fixture profiles
- [x] Training Slime resists slashing
- [x] Frosty Cheese Thing is vulnerable to fire
- [x] Suspicious Training Dummy is immune to poison
- [ ] Conditions
- [ ] Multi-type damage packets
- [ ] GMRC trait integration
- [ ] Server-side certification on deployment


### Phase IV.16 — Under Strange Afflictions

- [x] Canonical combat condition vocabulary
- [x] Persistent per-token conditions
- [x] Optional turn-based duration
- [x] Condition expiry at the end of the affected combatant's turn
- [x] DM-only apply/remove authority
- [x] Battle Events for application, removal and expiry
- [x] Chamber state exposes conditions
- [x] Token condition markers
- [x] DM Afflictions controls
- [x] Poisoned pixel-bubble presentation
- [x] Reduced-motion compatibility
- [ ] Mechanical modifiers imposed by individual conditions
- [ ] GMRC trait/spell automatic condition application


### Phase IV.17 — Afflictions Take Hold

- [x] Server-authoritative attack advantage/disadvantage modes
- [x] Two-d20 advantage and disadvantage resolution
- [x] Poisoned attacker has disadvantage
- [x] Blinded attacker has disadvantage
- [x] Prone attacker has disadvantage
- [x] Restrained attacker has disadvantage
- [x] Attacks against Blinded targets have advantage
- [x] Attacks against Restrained targets have advantage
- [x] Attacks against Stunned targets have advantage
- [x] Advantage and disadvantage cancel to a normal roll
- [x] Stunned blocks ordinary Battle Deeds
- [x] Grappled blocks token movement
- [x] Restrained blocks token movement
- [x] Stunned blocks token movement
- [x] Browser reports roll mode and both d20 rolls
- [ ] Prone target melee/ranged distinction (requires certified distance)
- [ ] Frightened attack penalty (requires source/line-of-sight context)
- [ ] Charmed source-target attack restriction
- [ ] Saving throw consequences
- [ ] Server-side certification on deployment
