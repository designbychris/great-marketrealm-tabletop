- IV.32.4A final browser polish: Guild Diceworks now spans the full Turn of Battle grid so certified attack results no longer collapse into the narrow status column.

## Phase IV.32.3A — The Deeds Remember Every Adventurer

- [x] Player Satchel rolls carry the Encounter visible in the live Chamber as a server-validated routing hint.
- [x] Active Encounter rolls remain in Deeds at the Table; Exploration rolls remain in Tales from the Chamber.
- [x] Quick Hands, Weapons to Hand and Spell Pouch share the same corrected Chronicle path.
- [x] Satchel scrollbar now matches the pixel Keeper-drawer scrollbar language.
- [ ] Browser-certify Player roll visibility during battle and after returning to Exploration.

- [x] IV.27E — Light Wrought by Magic: Companion-certified magical illumination in the Living Veil.
- [x] IV.27E corrective — The Satchel leaves the Chamber with its character token.

### Phase IV.27D.1 — The Little Flame
- Replaces the dropped-torch emoji with a tiny animated SNES-era pixel flame.
- Flame flicker and ember animation are presentation-only; authoritative illumination remains unchanged.
- Respects `prefers-reduced-motion`.

## Phase IV.27D — Fire Upon the Floor
Dropped torches are persistent scene-scoped world light sources. A lit carried torch may be dropped at the server-derived adventurer position, continues illuminating through the shared barrier-aware Living Veil, and may be recovered when the adventurer is close enough. Browser intent never supplies authoritative coordinates or light radii. THUNK. 🔥. REGRET.

## Phase IV.27B — The First Lantern
Carried, server-authoritative torchlight: 20 ft bright + 20 ft dim, bound to the player-controlled Companion token and clipped by Living Veil LOS barriers.
- IV.27A — The Adventurer's Sight: viewer-specific server-authoritative sight now consumes Companion-certified darkvision, converts feet to 5 ft grid squares, and prevents another player's character from extending your current visibility.
- IV.26G alpha.2 — Walking Shoes corrective: darker Fellowship-coloured footprints and distance-sampled trails (about one paired print per two 5 ft squares), still bounded and viewer-safe through the Living Veil.
## Phase IV.26F — Colours of the Fellowship

- Curated Great Marketrealm Table Palette with persistent player-selected Fellowship Ribbon colours.
- Stable deterministic defaults, authenticated server-side validation, and live propagation through Gathering, Chronicles, Satchel, and player-controlled battlefield tokens.
- Colour remains supplementary to labels, roles, names, and other ownership cues for accessibility.

## Phase IV.26D.2 — Clear the Battlefield & The Living Gathering

- [x] Obvious Choose Your Adventurer guidance when no Companion Character is selected.
- [x] Dungeon Master removal of Chamber tokens.
- [x] Player removal limited to their own Companion Character token.
- [x] Active encounter combatants protected from accidental token removal.
- [x] Adventurers at the Table patched through the existing Living Table heartbeat.
- [x] Invite/remove/member/Companion HP roster changes visible without manual refresh.
- [ ] Browser-certify live multi-user Gathering updates and token removal permissions.

## IV.26D — Adventuring Measures at the Table

Current HP and Temporary HP can now be adjusted from the Satchel through an owner-scoped Companion write boundary. Maximum HP remains Companion-certified and read-only.


### Phase IV.26B — Weapons to Hand
The Satchel now exposes equipped Companion weapons with authoritative attack and damage rolls. Target-aware battle resolution and shared DM roll visibility remain separate future surfaces.
# Great Marketrealm Tabletop Roadmap

## Phase IV.22B.1 — The Keeper's Cartography

- [x] Continuous wall tracing from the previous endpoint.
- [x] Live grid-snapped placement preview.
- [x] Select/highlight authored wall and door segments.
- [x] Undo the most recently authored segment.
- [x] Clarify doorway framing and open/closed authoring feedback.
- [ ] Browser-certify full-room tracing and door editing.

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


### Phase IV.18 — The Measure of the Battlefield

- [x] Server-authoritative square-grid token distance
- [x] Five-foot adjacency
- [x] Distance measured from token footprints
- [x] Combat Profiles persist normal and long attack range
- [x] Melee defaults to 5-foot reach
- [x] Out-of-range attacks rejected before spending the Attack deed
- [x] Long-range attacks impose disadvantage
- [x] Prone-target advantage within 5 feet
- [x] Prone-target disadvantage beyond 5 feet
- [x] Read-only target measurement endpoint
- [x] Live target line from current attacker to selected target
- [x] Live IN RANGE / LONG RANGE / OUT OF RANGE feedback
- [x] Range preview exposes expected roll mode
- [x] Guild Diceworks combat tray
- [x] Two visible d20s for Advantage / Disadvantage
- [x] Chosen and rejected dice presentation
- [x] Natural 1 lonely pixel confetti
- [x] Reduced-motion support
- [x] Test Table includes melee and ranged fixture profiles
- [ ] Cover and line of sight
- [ ] Opportunity attacks
- [ ] Area templates
- [ ] Movement budgets
- [ ] Hex-grid measurement
- [ ] Server-side certification on deployment


### Phase IV.19 — The Chronicle of Battle

- [x] Guild Diceworks owns immediate attack result presentation
- [x] Hit / miss / critical result displayed beside the dice
- [x] Attack arithmetic displayed beside the dice
- [x] Damage type, RESIST / WEAK / IMMUNE and HP displayed beside the dice
- [x] Lower live status becomes concise operational feedback
- [x] Persisted Battle Events projected into readable Chronicle entries
- [x] Attack + damage event pairs merge into one Chronicle entry
- [x] Attack deed duplicate suppressed from Chronicle
- [x] Non-attack deeds chronicled
- [x] Death saves chronicled
- [x] Condition application / removal / expiry chronicled
- [x] Chronicle limited to latest 12 entries
- [x] Newest Chronicle entry appears first
- [x] Hidden-token events do not leak through Player Chronicle
- [x] Chronicle refreshes with Tabletop state
- [ ] Full encounter export
- [ ] Search/filter Chronicle
- [ ] DM annotations
- [ ] Server-side certification on deployment


### Phase IV.19.1 — Chronicle & Fallen Combatant Presentation

- [x] Chronicle member-list CSS collision removed
- [x] Chronicle rows grow naturally with wrapped content
- [x] Chronicle receives a readable scroll viewport
- [x] Server-authoritative combatant presentation states
- [x] Healthy / Wounded / Downed / Defeated / Deceased distinction
- [x] 0 HP character is Downed, not automatically dead
- [x] 0 HP creature/object is Defeated
- [x] Three failed death saves / confirmed death is Deceased
- [x] DOWN / KO / DEAD token state badges
- [x] Semantic token CSS/data hooks for future pixel sprites
- [x] State refresh remains server-driven
- [x] Party HP can surface terminal combatant state
- [ ] Pixel-art fallen poses/icons
- [ ] Creature-specific death-save policy
- [ ] Server-side certification on deployment


### Phase IV.20 — The Arsenal of the Adventurer

- [x] Persistent multi-attack Combat Arsenal
- [x] Melee weapon / ranged weapon / natural / spell / improvised kinds
- [x] Per-attack modifier, range and damage profile
- [x] Attack selection HUD
- [x] Selected attack drives authoritative targeting and resolution
- [x] Legacy single attack remains a compatibility fallback
- [x] Attack identity written to Battle Events
- [x] Tabletop-native monster/NPC arsenals
- [x] Explicit future GMRC `CompanionArsenalSource` boundary
- [x] Opaque Companion character references; no hard plugin namespace dependency
- [x] Two Training Grounds attacks per combatant
- [x] Battlefield horizontal scrollbar removed
- [ ] Live GMRC adapter implementation
- [ ] Ammunition/equipment consumption
- [ ] Spell-resource consumption
- [ ] Server-side certification on deployment


### Phase IV.21 — The Cartographer's Table

- [x] Dungeon Master battlemap chooser in the active Scene
- [x] WordPress Media Library image upload/selection
- [x] Image-only server validation
- [x] Persist selected Media attachment ID on the Scene
- [x] Adopt the selected image's native dimensions
- [x] Preserve token normalised coordinates when artwork changes
- [x] Preserve grid type and grid size when artwork changes
- [x] Live battlemap refresh after successful save
- [x] Active Dungeon Master authorization and nonce protection
- [x] Existing bundled Training Yard remains a valid fallback/current map
- [x] Grid scale/offset calibration — Phase IV.21A


### Phase IV.21A — The Cartographer's Grid

- [x] Live square-size calibration
- [x] X/Y visual grid offsets
- [x] Grid opacity and visibility
- [x] One-pixel nudge controls
- [x] Resettable preview before save
- [x] Persistent DM-authoritative Scene calibration
- [x] Backwards-compatible defaults for existing Scenes

### Phase IV.21B — The Cartographer's Lens

- [x] Zoom battlefield in/out
- [x] Fit battlemat to chamber viewport
- [x] Reset to 100% view
- [x] Drag-to-pan while zoomed
- [x] Transform map, grid and tokens together
- [x] Keep lens state independent from combat persistence

### Phase IV.21B.1 — The Steady Lens

- [x] Disable native battlemat image dragging
- [x] Prevent battlefield text/image selection during pan
- [x] Anchor pan movement to pointer-down origin
- [x] Capture pointer for the complete gesture
- [x] Add movement threshold before camera pan begins
- [x] Preserve token and control interactions

### Phase IV.22 — The Veil of the Unknown

- [x] Per-Scene Fog of War toggle
- [x] Unexplored areas fully veiled
- [x] Explored areas remembered but dimmed
- [x] Current party-visible cells fully clear
- [x] Character movement expands exploration memory
- [x] DM sees through the veil
- [x] DM can preview Player Fog
- [x] DM can reset exploration
- [x] Fog aligns with calibrated grid
- [x] Non-character tokens outside current Player vision are withheld from Player state
- [x] Lens remains presentation-only over the same fog coordinate space
- [x] Walls and doors block line of sight
- [ ] Per-character vision distances / darkvision
- [ ] Light-source rules
- [ ] Manual polygon reveal/hide

### Phase IV.22A — The Living Veil

- [x] Replace harsh per-cell fog gradients with cohesive darkness
- [x] Keep explored territory visible as dim memory
- [x] Add pixel-dithered reveal boundary
- [x] Add separate unexplored-to-memory boundary treatment
- [x] Preserve Fog mechanics and persistence unchanged
- [x] Respect reduced-motion preference

### Phase IV.22A.1 — The Anchored Veil / The Veil Remembers

- [x] Project current vision from canonical Scene character tokens before viewer payload filtering
- [x] Keep player token filtering independent from vision-source calculation
- [x] Regression-test exploration memory across encounter persistence and repository reload
- [x] Preserve explicit Reset Exploration as the clearing path
- [x] Persist rendered grid calibration reference width
- [x] Translate visual grid pixels into intrinsic server map coordinates
- [x] Scale Fog cells to each viewer's rendered battlefield
- [x] Preserve Player Fog preview across End Turn reloads
- [x] Prompt older Scenes for one Grid Save to establish their Fog anchor
- [x] Remove stale DOWN HUD immediately after natural-20 death-save revival
- [x] Synchronize stable/deceased death-save HUD states

- **Phase IV.22A.1 — The Veil Remembers (browser corrective pass)** — corrective browser pass for true token dragging and resilient rendered vision anchoring.

### Phase IV.22B — Sight Beyond the Door

- [x] Persist a per-Scene Dungeon Master vision layer
- [x] Draw wall segments snapped to calibrated grid intersections
- [x] Place door segments on the same grid geometry
- [x] Closed doors block current sight
- [x] Open doors permit current sight
- [x] Walls block server-projected Fog visibility
- [x] Player token filtering uses the same blocker-aware server projection
- [x] Keep DM vision-layer geometry out of Player chamber state
- [x] Preserve blocker-aware explored cells as the party's remembered route
- [x] Provide DM door toggle and barrier removal controls
- [ ] Browser-certify wall drawing against a real calibrated dungeon map
- [ ] Browser-certify closed/open door transitions and remembered-room behaviour


## Phase IV.23 — The Gathering at the Table

Real WordPress identities, avatars, Table invitations and explicit seat acceptance establish the human membership layer before Companion character assignment and the Pixel Chamber. GMRC remains behind an integration boundary and Tabletop remains independently usable.


### Phase IV.23A — The Summons to the Table

**Corrective alpha.2:** reliable query-route summons links, Companion sign-in wording, and DM player removal/reinvite support.
- [x] Persist the Tabletop invitation before attempting delivery.
- [x] Send a WordPress email summons containing the direct Table URL.
- [x] Use the configured WordPress admin email as the explicit summons sender when valid.
- [x] Preserve the invitation and report honest UI feedback if `wp_mail()` fails.
- [x] Publish a Companion-safe invitation event without writing into GMRC internals.
- [x] Provide an avatar integration filter for Companion profile imagery.
- [x] Distinguish DM and Player seats with role accents while keeping presence a separate signal.
- [ ] Browser-certify real email delivery and the received Take My Seat link.
- [ ] Browser-certify Companion-side notification once GMRC consumes the integration event.

## Phase IV.23A.1 — The Table Keeps Time
- [x] Reuse the existing chamber heartbeat rather than introduce a competing poller.
- [x] Publish a server-derived shared-state revision with chamber state.
- [x] Detect remote Encounter ID/revision changes and automatically re-render stale chambers.
- [x] Preserve lightweight in-place token, Fog, Vision Layer and Chronicle refreshes.
- [ ] Browser certify DM End Turn updates a connected Player's round/turn without manual refresh.
- [ ] Browser certify Fog/exploration remains intact through the synchronized re-render.

## Phase IV.23A.2 — The Living Table

- [x] Reuse the existing authoritative chamber heartbeat.
- [x] Patch remote round changes in place without reloading the chamber.
- [x] Patch the active combatant label in place.
- [x] Move the active-turn battlefield marker in place.
- [x] Continue live Chronicle, token, Fog and Vision Layer refreshes.
- [x] Preserve a safe reload fallback for encounter lifecycle changes until IV.24.
- [ ] Browser-certify DM → Player turn/round updates without a visible page reload.


## Phase IV.24 — Peace and Battle

- [x] Treat an active Scene with no current Encounter as Exploration Mode.
- [x] Keep token movement, Fog, remembered exploration, walls and doors available during Exploration.
- [x] Give the Dungeon Master an explicit Start Encounter workflow.
- [x] Choose participating Scene tokens and initiative before battle begins.
- [x] Give the Dungeon Master an explicit End Encounter action.
- [x] End battle without resetting Scene/Fog/Vision state.
- [x] Replace encounter-lifecycle full-page reloads with a live Chamber transition.
- [ ] Browser-certify DM and Player move together from Exploration → Encounter without a page reload.
- [ ] Browser-certify Encounter → Exploration preserves token positions, doors and remembered route.

- IV.24 alpha.2 corrective: live Exploration/Encounter transitions now fetch viewer-specific Chamber markup through authenticated AJAX and patch only lifecycle/Chronicle regions, preserving the battlefield DOM and preventing cached DM markup from leaking into Player presentation.

### 0.24.0-alpha.3 — Peace and Battle live-state repair
- Restores the persistent lifecycle and Battle Chronicle DOM anchors required by the Living Table heartbeat.
- Player Exploration/Encounter transitions patch only live lifecycle regions, keeping the battlefield and Fog heartbeat alive.
- DM Begin Battle / End Encounter rebuild through the authenticated Tabletop fragment endpoint so newly rendered combat controls receive complete bindings.
- Removes the cached frontend-page fetch from live Chamber rebuilding.

### Phase IV.25 — The Companion Character Gate
Bind a seated WordPress user to an owner-validated Companion Character and its forged battlefield token, establishing player token ownership before deeper Character Sheet projection.


### Phase IV.25.1 — The Character Bears Their Token

Correct the final forged-token presentation boundary so Companion-generated portrait fallbacks render on the Tabletop for both Keeper and Player views while preserving the secure Character Gate ownership chain.


## 0.25.0-alpha.3 — Phase IV.25.2: The Keeper Keeps Pace

- Keeps the Keeper's local Encounter revision stale until the authoritative Living Table refresh arrives after End Turn.
- Lets the DM consume the same in-place round/current-combatant/active-token patch already proven on Player browsers.
- Restores the End Turn control after a successful refresh instead of leaving it disabled as “Passing…”.
- Companion-side owner-aware portrait projection accompanies this corrective so a DM sees the seated player's actual forged/custom token artwork rather than a viewer-scoped generated fallback.


### Phase IV.26 — The Adventurer's Satchel (0.26.0-alpha.1)
- Tabletop support for the owner-scoped tabletop play projection and pull-out Adventurer's Satchel.
- Companion remains authoritative for character mechanics; Tabletop consumes the projection without duplicating character persistence.


### Phase IV.26 Browser Corrective — 0.26.0-alpha.2
- Restore Player Chamber rendering for seated Companion characters by routing Satchel artwork through the established Companion token image-source boundary.


### Phase IV.26A — Quick Hands at the Table (0.26.1-alpha.1)
- Turns Satchel abilities, saving throws, skills and Initiative into accessible d20 roll controls.
- Resolves the seated Companion Character and modifier server-side; the browser never supplies its own modifier.
- Uses the existing cryptographically secure d20 roller and reports natural 20/1 as presentation flourishes rather than automatic check/save outcomes.
- Works from the Satchel in both Peace and Battle modes.

- [x] IV.26C — The Spell Pouch: unfurled Satchel and Companion-authoritative spell projection.


### Phase IV.26C.3 — Magic at Your Fingertips

- [x] Roll Spell Pouch spell attacks from authoritative Companion projection.
- [x] Roll spell damage from authoritative projected dice/formula.
- [x] Roll healing separately from damage and include the projected spellcasting modifier.
- [x] Browser submits only `spell_id` and `spell_action`; Tabletop re-resolves the seated owned Companion Character.
- [ ] Later: resolve saving throws against actual battlefield targets instead of asking the caster to roll the target save.
- [ ] Later: expend/restore live spell slots through the Companion-authoritative play boundary.

### Next after IV.26C.3

- Adventuring Measures at the Table: repair/confirm authoritative current, maximum and temporary HP presentation and safe live HP controls.
- Chronicle the Satchel: encounter-time Satchel rolls enter the Battle Chronicle; Exploration/Peace Mode rolls enter a persistent Chamber Chronicle.

- [x] IV.26D.1 — One Measure of the Adventurer: Satchel and Gathering share Companion-authoritative Adventuring Measures.


### Phase IV.26E — Chronicles of the Table
- [x] Shared server-authoritative Satchel Chronicle recorder.
- [x] Quick Hands, Weapons to Hand, and Spell Pouch recording.
- [x] Encounter rolls routed into Battle Chronicle.
- [x] Peace/Exploration rolls routed into persistent Chamber Chronicle.
- [x] Living Table heartbeat projects Chronicle changes without a second poller.
- [ ] Browser certification.

### Phase IV.26G — Footsteps Through the Veil ✅ IMPLEMENTED / BROWSER CERTIFICATION PENDING
- Bounded recent player-character movement trails.
- Fellowship-coloured pixel footprints with directional orientation and progressive fading.
- Server-authoritative viewer projection through current/remembered Living Veil state.
- No second polling loop; footsteps ride the existing Living Table heartbeat.
- Next: Phase IV.27 — Lanterns in the Living Veil (vision profiles, darkvision and light sources).

## IV.28 — The Keeper's Atlas

- [x] **IV.28A — The Atlas Register** — DM-only persistent multi-Scene register, add maps without disturbing the active Scene, and explicitly open a retained Scene.
- [x] **IV.28B — Behind the Curtain** — Keeper's Atlas DM drawer and private inactive-Scene preparation. Preparation projections and cartography/Fog/vision/token movement are explicitly bound to the selected Scene while players remain on the live Scene.
- [x] **IV.28B.1 — The Keeper Clears the Shelves** — safely remove inactive Scenes and their Scene-owned battlefield state while protecting the live and final Scene.
- [x] **IV.28C — Passage Between Places** — live viewers detect the Keeper's authoritative Scene change through the existing Living Table heartbeat and rehydrate the full Chamber without a browser page reload; private Behind-the-Curtain preparation remains pinned to its selected Scene.
- [x] **IV.28D — The Threshold Markers** — DM-only Party Arrival and Monster Deployment markers; first-time adventurers are forged into a newly opened Scene at Party thresholds while existing Scene-owned character tokens retain their remembered positions.
- [x] **IV.29 — The Keeper's Bestiary** — umbrella phase for the Keeper's private creature catalogue, authoritative Scene summoning, and complete battlefield combat integration.
- [x] **IV.29A — The Keeper's Bestiary** — DM-only searchable creature-definition drawer with the combat-certified Training Grounds trio; definitions remain separate from battlefield instances.
- [x] **IV.29B — Summoned to the Table** — DM-only creature deployment into live or privately prepared Scenes, with manual map placement, Monster Deployment Threshold placement, bounded multi-summon groups, and optional hidden-from-Players visibility.
- [x] **IV.29C — Creatures in Battle** — deployed Bestiary instances receive authoritative Vitality, Combat Profiles, Damage Defenses and Combat Arsenals, then flow through the existing initiative, condition, targeting, combat and Chronicle systems.
- [x] **IV.29C.1 — Eyes on the Enemy** — combat controls move out of Turn of Battle: Players act from the Satchel, Keepers act from the highlighted active Bestiary instance, battlefield targeting is shared, and Turn of Battle becomes authoritative status/legality plus Keeper turn advancement.
- [x] **IV.29C.1A — Roll for Damage** — successful authoritative attacks pause after the d20 result so the acting Player or Keeper explicitly rolls the server-owned weapon/creature damage in Guild Diceworks; critical dice, defenses, HP and death consequences remain authoritative and each earned damage roll is single-use.
- [x] **IV.29D — The Keeper's Menagerie** — external/custom Bestiary source boundary for expanding the catalogue, including Companion-backed canonical Great Marketrealm creature records where available.
- [x] **IV.29D.1 — Menagerie Filters** — Keeper-only All / On This Map / Not On This Map filtering, Scene-aware deployment counts, and per-creature on-map instance badges for faster encounter navigation.
- [x] **IV.30 — Keeper's Cartography Assistant** — DM-only browser-side artwork analysis against the calibrated square grid suggests room/wall boundaries and possible doors as a private review draft; only Keeper-selected suggestions become authoritative vision barriers.
- [x] **IV.30.1 — Structural Cartography** — adds review-first Structural tracing for common thick-ink, hatch-textured dungeon maps; continuous architectural boundaries are favoured over repetitive stone texture before being converted back into existing grid barrier geometry.
- [x] **IV.30.1A — Curves & Continuity** — extends Structural tracing with diagonal boundary passes, conservative one-sample continuity repair, and short connected barrier approximations for curved/organic architecture while preserving the review-first draft and hatch-noise rejection.
- [x] **IV.30.1B — The Living Contour** — adds a complementary cave/organic floor-boundary tracer that turns quiet-floor versus hatched-rock transitions into reviewable LOS contours without destabilising constructed-dungeon Structural tracing.
- [x] **IV.30.1B.1 — Fine Contour Sampling** — decouples Living Contour analysis density from gameplay-grid density with an adaptive fine mesh and precise fractional LOS endpoints, while preserving bounded review-first application.
- [x] **IV.30.1B.2 — Contour Simplification & Full-Boundary Tracing** — traces every connected fine-mesh cave boundary before simplifying complete chains/cycles into a bounded review set, preventing scan-order truncation while retaining meaningful bends.
- [x] **IV.30.1B.3 — The Cartographer's Economy / Adaptive Contour Reduction** — distributes the 200-suggestion review budget across complete cave contours by boundary importance, then simplifies each chain to its own allocation so full-dungeon coverage is favoured over either scan-order truncation or one-size-fits-all tolerance.
- [x] **IV.30.1B.3A — Contour Topology Guard** — keeps adaptive cave simplification local to each ordered boundary span, bounding replacement chord length, deviation and detour so review-budget compression cannot invent cross-room or cross-map LOS diagonals.
- [x] **IV.30.1C — The Cartographer's Linework / Polyline Vision Barriers** — teaches the authoritative vision layer to persist ordered multi-vertex wall paths, lets LOS resolve every span inside a path, and lets Living Contour review/apply whole cave boundaries as path objects instead of spending one barrier record per bend.
- [x] **IV.30.1D — The Cartographer's Judgement / Hybrid Structural & Living Contour Analysis** — runs the constructed-wall and organic-floor readers together, uses local repeated-line evidence to favour structural geometry, preserves Living Contour polylines through irregular regions, and suppresses ambiguous overlap rather than bridging genuine openings.
- [x] **IV.30.1D.1 — The Connected Dungeon / Playable-Floor Connectivity & Region Graph** — builds connected playable-floor regions for Hybrid Judgement, conservatively heals thin low-confidence ink/grid seams between floor samples, suppresses tiny noise components, and lets structural evidence retain genuine constructed walls and openings.
- [x] **IV.30.1E — The Cartographer's Lens Controls** — places Zoom In, Zoom Out, live zoom percentage, Fit and Reset directly over the battlemap while reusing the existing Cartographer's Lens transform/pan state and preserving client-only authority.
- [x] **IV.30.1F — The Cartographer's Registration / Printed-Grid Registration & Calibration Intelligence** — analyses repeated horizontal/vertical artwork linework in the Keeper's browser, previews a suggested square size and X/Y phase against the existing live grid controls, and requires explicit Save Grid authority before persistence.
- [x] **IV.30.1F.1 — The Surveyor Learns the Difference Between a Grid and a Wall** — biases printed-grid registration toward thin pale linework inside quiet floor, rejects heavy architectural ink, requires repeated evidence on both axes, and resolves supported room-size harmonics back to the smaller fundamental artwork grid.

- [x] **IV.30.2 — The Cartographer's Dungeon Forge** — deterministic geometry-first dungeon drafting and Keeper-authoritative build: connected rooms/corridors become persistent native artwork, calibrated grid, walls, closed doors, Keeper lights and a freshly enabled Living Veil. Drafts remain local until Build Dungeon.
- [x] **IV.30.2A — The Forge Creates Worlds** — adds Atlas → Generate Dungeon so the Keeper can forge a brand-new generated-surface Scene without a background image, choose a deterministic scale/seed and Great Marketrealm floor/rock theme, then inspect the fully built grid/walls/doors/Fog/lights Behind the Curtain before opening it to Players.

- [x] **IV.30.2A.1 — Pippin Decorates the Place** — upgrades Forge themes from palette swaps to deterministic procedural floor/rock treatments while preserving identical geometry, walls, doors, Fog and lights; generated surfaces also suppress stale image presentation.

- [x] **IV.30.2B — Beyond the Dungeon Walls** — promotes Dungeon Forge into a deterministic Scene Forge. Environment selects topology (`Dungeon`, `Forest`, `Village`) while Theme remains presentation-only; Forests gain clearings, trails and environmental LOS obstacles, Villages gain roads, a square, buildings, doors, gardens, wells and tactical obstruction, all projected through the existing grid/Vision/Fog/light/encounter systems.


### Phase IV.31 — The Keeper's Lantern Rack ✅ IMPLEMENTED
DM-placeable Scene lighting through the existing Living Veil illumination machinery; browser certification pending.

- [x] **IV.31.1 — The Keeper Strikes a Match** — places every Keeper source lit by default, exposes Lit/Doused state with inverse Douse/Light controls, certifies Torch 20 ft / Lantern 30 ft / Brazier 60 ft / Candle 10 ft / Magical Light 40 ft bright radii, and sizes battlefield glows from the authoritative projected range.


### Phase IV.30.2A.1A — The Mystery of the Corner Tile
- Trace and remove the stray top-left generated-battlefield artefact.
- The initial Atlas-emoji hypothesis was disproved by browser certification: the artefact survives with the emoji removed.
- Forensic pass II inspects real bounding-box overlaps, including `pointer-events:none` token/effect descendants that `elementsFromPoint()` cannot see.
- The trace now reports token/light IDs, labels, image filenames and token X/Y CSS coordinates and repeats after asynchronous rendering.

**Status:** CLOSED / browser-certified. Root cause was normalised Forge barriers being consumed as rules-grid coordinates, compressing the complete dungeon wall network into the top-left grid square. The server now converts Forge surface coordinates to rules-grid coordinates before Vision persistence.


### IV.30.2A.1A — Corner Tile Forensic Pass IV: The Wider Search
Diagnostic corrective: widen the corner investigation to the complete live document so runtime/theme/Elementor-injected text, images, pseudo content, or CSS backgrounds can be identified by their actual owner.

- [x] **IV.30.2B.1 — Pippin Discovers That Trees Aren't Rooms** — outdoor Forge presentation separates organic scenery from authoritative rules geometry.
- **IV.30.2B.2 — Pippin Learns to Colour Inside the Lines** — hides Forge-owned environmental LOS geometry at rest while preserving its rules behaviour, and adds a richer deterministic visual vocabulary for trees, rocks, logs, village buildings, wells and gardens.

### Phase IV.32 — The Table Becomes Pixel
A staged SNES-era presentation pass for the Tabletop. Pixel styling remains a skin over the existing certified rules engine so visual work cannot silently change geometry or encounter behaviour.

- [x] **IV.32.1 — The Keeper Finds the Pixel Chisel** — establishes reusable pixel design tokens and applies the first stepped-border, hard-shadow, square-control treatment to the chamber shell, battlefield frame, common controls and Keeper tool strips while preserving focus, disabled and reduced-motion states.
- [x] **IV.32.2 — The Atlas Gets a 16-Bit Makeover** — extends the Pixel Chisel into the Keeper's Atlas, Scene register, Forge, Threshold, Cartography and Lantern Rack surfaces with stepped drawer framing, pixel Scene records, explicit generated/active states, compact disclosure markers and unified workbench styling while leaving all authority and behaviour unchanged.
- [x] **IV.32.2A — Pippin's Field Notes** — integrates canonical full-art and pixel Pippin into the Scene Forge with a reusable contextual Field Note component.
- [x] **IV.32.2B — Pippin Demands a Bigger Office** — widens the Atlas and Bestiary into shared responsive Keeper workspaces, gives Scene/creature cards room to breathe, enlarges Pippin's Field Note presentation, and restructures The Gathering rail so portraits, names, roles, actions, HP and the Fellowship Ribbon fit the pixel UI without crowding.
- [x] **IV.32.2C — The Keeper Rearranges the Furniture** — turns Atlas/Bestiary tabs into one moving Keeper rail, compacts Table/Scene/mode identity into a full-width command header, and gives Gathering seats character portraits (or `P`) plus distinct management and vitality bands without changing Tabletop authority.
- [x] **IV.32.3 — Adventurers in Miniature** — carries the 16-bit language into live play: Gathering seats become fellowship-coloured miniature records with authoritative current-turn highlighting, Turn of Battle becomes a compact pixel status plaque, and Players/Keepers share one active-turn visual grammar across Satchel, Bestiary and battlefield tokens without changing encounter authority.

### Phase IV.32.2C.1 — The Keeper Tidies the Desk
Browser-feedback polish for the pixel Tabletop shell: align the Bestiary workspace with the Atlas top edge, preserve live character-seat portraits, restore Fellowship Ribbon colours, consolidate Keeper battlefield tools behind one disclosure, remove obsolete Lens help copy, and compact the DM-only Companion adventurer picker.

- [x] **IV.32.3 — Adventurers in Miniature** — gives current-turn party seats, battlefield miniatures, Bestiary combatants and the battle status plaque one shared 16-bit turn grammar without changing encounter authority.
- [x] **IV.32.3A — The Deeds Remember Every Adventurer** — routes Player Satchel rolls into the active Battle Chronicle when an encounter is visible and keeps exploration rolls in the Chamber Chronicle; also shares the Keeper drawer scrollbar language with the Satchel.
- [x] **IV.32.3B — The Keeper Rolls Behind the Screen** — adds a DM-only, server-authorised Secret d20 using the certified secure roller; results remain response-only in the Keeper browser and never enter shared Chronicle or Living Table state.
- [x] **IV.32.4A — The Battlefield Finds Its Pixels** — carries the established 16-bit grammar onto the calibrated grid, token selection/current-turn cursors, targeting line/range plaque, Keeper thresholds, Vision doors and existing Footsteps trail without changing battlefield authority or geometry; browser-certification polish also lets the five combat deed controls wrap responsively rather than allowing long labels such as `Disengage` to escape a narrow dock.

- **IV.32.4A final polish** — responsive certified attack-result layout plus authoritative turn-boundary cleanup for transient Guild Diceworks results.
