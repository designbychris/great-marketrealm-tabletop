## Phase IV.34.2 — The Table Remembers Tonight

- Adds Pippin's Table Atlas control for linking an owned Tabletop to an active Dungeon Master Companion Campaign.
- Uses explicit cross-plugin WordPress contracts; Tabletop does not load or mutate Companion classes directly.
- Backfills existing persistent Tabletop Sessions into the Companion Session Ledger when a campaign link is established.
- Synchronises each future Session start/end to the same Companion Ledger identity.
- Projects the Companion Campaign and linked Fellowship name back into the Table Atlas.
- Keeps the Fellowship Company Chronicle publication boundary for IV.34.3.

- IV.32.6A polish: removed the in-Chamber masthead from Door/invitation threshold states and made both cinematic thresholds fill the remaining viewport beneath the site header without a bottom gap.
## Phase IV.32.4C — The Keeper Lights the Pixels

- Replaces platform emoji on Keeper environmental battlefield lights with CSS-drawn 16-bit Torch, Candle, Lantern, Brazier and Magical Light markers.
- Gives each lit environmental source a restrained stepped animation personality while leaving its server-projected radius and Fog/LOS contribution unchanged.
- Doused lights remain Keeper-visible but dormant, and `prefers-reduced-motion` disables the new motion without removing the static light presentation.
- Adds regression protection for pixel markers, per-light motion vocabulary, dormant doused state, reduced motion and the certified Lantern Rack radii.

## Phase IV.32.4B — The Veil Goes 16-Bit

- Gives unexplored Fog a restrained near-black 16-bit dither while preserving complete concealment.
- Recasts explored-but-not-currently-visible territory as a faded, desaturated map memory.
- Adds stepped presentation for the existing live-sight and memory frontiers without changing projected visibility.
- Gives the Keeper's existing Player Fog Preview a private pixel frame/label; no preview state is exposed to Players.
- Keeps Fog persistence, LOS, doors, lights, movement, vision radii and token-visibility authority unchanged.

- IV.32.4A final browser polish: Guild Diceworks now spans the full Turn of Battle grid so certified attack results no longer collapse into the narrow status column.

## Phase IV.32.3A — The Deeds Remember Every Adventurer

- Corrects Player Satchel roll Chronicle routing during an active Encounter using a server-validated encounter hint from the live Chamber.
- Preserves the authoritative Battle Chronicle / Chamber Chronicle split and DM roll behaviour.
- Gives the Adventurer's Satchel the shared dark pixel scrollbar treatment used by Atlas/Bestiary.



- IV.30.2A.1A Corner Tile Forensic Pass IV — The Wider Search: expands the generated-corner diagnostic beyond the battlefield subtree, inventorying whole-document overlapping elements, Range-based text/emoji glyphs, image/SVG-image sources, pseudo content, and accessible stylesheet background/content rules.
## 0.32.0-alpha.7 — IV.30.2A.1A Corner Tile Forensics III

- Extends the generated-scene corner probe beyond `elementsFromPoint()` so `pointer-events:none` battlefield descendants can no longer evade inspection.
- Reports overlapping token/light/image elements, token IDs, labels, image filenames, and authoritative token X/Y CSS coordinates.
- Re-runs the probe after asynchronous chamber rendering and watches the token layer for mutations, making late-rendered origin artefacts visible to the trace.

- IV.30.2A.1A forensic pass III: generated Scenes now expose computed painter geometry/styles for a DM-only corner provenance trace that reports the real DOM/image/background/pseudo/text owner at the battlefield origin, so the persistent corner tile can be identified from browser evidence rather than another visual guess.
## 0.32.0-alpha.4 — Phase IV.30.2A.1A: The Mystery of the Corner Tile

- Identified the mysterious generated-map corner tile as the colourful `🗺️` emoji used by the fixed Keeper's Atlas drawer toggle, not dungeon geometry, artwork, Fog, or a leaked battlemap image.
- Removed the platform-rendered emoji from the Atlas toggle and retained a clean vertical `Atlas` control, preventing the operating system/browser emoji glyph from presenting as a stray map tile over the battlefield.
- Added a regression contract protecting generated battlefields from reintroducing that colourful corner-map glyph.
- Version: `0.32.0-alpha.4`.

## 0.32.0-alpha.4 — Phase IV.30.2A.1: Pippin Decorates the Place

- Turns Dungeon Forge themes into deterministic procedural surface treatments rather than palette-only recolours.
- Adds theme-specific floor marks: stone cracks, butcher-cellar mortar/drains, Rootland roots/pebbles, Frostreem ice fractures, Bakery brickwork/crumbs, and Mushroom Grotto fungal details/spores.
- Adds deterministic rock-face accents along playable boundaries while leaving gameplay topology untouched.
- Keeps theme decoration presentation-only: floor cells, walls, doors, grid registration, Fog and Keeper light coordinates remain authoritative and unchanged.
- Hardens generated Scenes against stale image presentation by suppressing battlemap image layers on generated surfaces.
- Version: `0.32.0-alpha.4`.

## 0.32.0-alpha.2 — Phase IV.30.2A: The Forge Creates Worlds

- Adds **Generate Dungeon** directly to the Keeper's Atlas; no WordPress Media background image is required.
- Introduces explicit generated Scene surfaces while preserving image-backed Scene validation and backwards-compatible old records.
- Creates the new Scene, grid, forged SVG artwork, vision walls, closed doors, Living Veil Fog and Keeper lights as one Atlas workflow.
- Opens successful forged worlds Behind the Curtain for private preparation instead of changing the live Player Scene.
- Cleans up the newly created Scene if the authoritative Forge build fails, avoiding half-built Atlas entries.
- Adds six visual stone/floor treatments: Pantry Stone, Butcher Cellar, Rootland Cavern, Frostreem Vault, Bakery Crypt and Mushroom Grotto. Themes repaint only the generated SVG; gameplay geometry remains authoritative and deterministic.
- Extends the existing in-Scene Dungeon Forge with the same Stone & Floor selector.
- Version: `0.32.0-alpha.2`.

## 0.32.0-alpha.1 — Phase IV.30.2: The Cartographer's Dungeon Forge

- Adds a Keeper-only deterministic Dungeon Forge that turns a seed into connected rooms and corridors directly inside the current Scene.
- Renders the forged dungeon as persistent Tabletop-native SVG artwork layered beneath the authoritative gameplay grid; no external image-generation service is required.
- Derives bounded wall and closed-door vision barriers from the generated floor topology and applies them through the existing authoritative Vision Barrier pipeline.
- Calibrates the existing square gameplay grid to the forge cells before build, rather than creating a second grid system.
- Suggests and places canonical Keeper lights using the Lantern Rack radii, with a brazier in the principal chamber and additional torch/lantern/magical sources through the route.
- Enables and resets the Scene's existing Living Veil Fog when the Keeper explicitly chooses **Build Dungeon**.
- Keeps **Forge Draft**, **New Seed**, and **Clear Draft** entirely preview-only; nothing persists until Build Dungeon.
- Persists the accepted forge plan per Scene so Players and Keepers see the same generated dungeon after refresh and through Behind-the-Curtain preparation.
- Version: `0.32.0-alpha.1`.

## 0.30.1-alpha.12 — Phase IV.30.1F: The Cartographer's Registration

- Adds Keeper-only **Find Printed Grid** intelligence to the existing Calibrate Grid panel.
- Analyses the already-loaded battlemap locally for repeated thin horizontal and vertical line evidence, then searches for a square periodicity supported on both axes.
- Suggests gameplay-grid square size and the nearest equivalent X/Y registration phase as a live preview, without creating a second grid model.
- Keeps detection preview-only: the existing Save Grid action remains the sole authoritative persistence boundary.
- Fails conservatively when printed-grid evidence is weak and leaves manual calibration fully available.
- Establishes the hostile misaligned-grid dungeon as the primary browser-certification benchmark.
- Version: `0.30.1-alpha.12`.

## 0.30.1-alpha.11 — Phase IV.30.1E: The Cartographer's Lens Controls

- Moves the Cartographer's Lens zoom, fit and reset controls into a compact fixed overlay directly on the battlemap.
- Reuses the established Lens scale/translation state and drag-to-pan behaviour rather than introducing a parallel map-navigation system.
- Adds live zoom percentage, accessible labels/tooltips, focus-visible treatment and automatic disabled states at the existing 25% / 300% zoom bounds.
- Gives Fit Map a small viewport gutter so artwork does not sit flush against the Lens edge.
- Keeps all view manipulation client-side; no authoritative Scene, grid, Fog, token or cartography state is mutated.
- Version: `0.30.1-alpha.11`.

## 0.30.1-alpha.10 — Phase IV.30.1D.1: The Connected Dungeon

- Added a playable-floor connectivity pass to Hybrid Judgement before structural and organic linework are merged.
- Conservatively heals one fine-analysis-cell seams only when quiet floor exists on opposite sides and the seam is below the strong-rock threshold.
- Builds orthogonally connected floor components and suppresses tiny disconnected white pockets caused by hatch gaps, handwriting and decoration.
- Keeps standalone Living Contour unchanged; connectivity healing is requested only by Hybrid Judgement.
- Genuine constructed walls remain recoverable through Structural tracing, while ambiguous openings remain review-first and are never auto-saved.
- Version: `0.30.1-alpha.10`.

## 0.30.1-alpha.9 — Phase IV.30.1D: The Cartographer's Judgement / Hybrid Structural & Living Contour Analysis

- Added a new Keeper-only `Judgement · hybrid map` Cartography Assistant mode for mixed constructed/organic dungeon artwork.
- Runs Structural tracing and Living Contour together, then scores repeated nearby parallel structural evidence to distinguish deliberate rooms/corridors from isolated handwriting, stair and hatch flecks.
- Suppresses Living Contour spans only where strong local structural evidence already represents that same wall; remaining organic runs stay ordered polyline paths and are never rejoined across an opening.
- Preserves the 200-object review ceiling, prioritising information-dense organic paths before spending the remaining budget on strongest locally-supported constructed linework.
- Review labels identify Hybrid · structural and Hybrid · organic suggestions so Keepers can see Pippin's local judgement before applying anything.
- Keeps the existing review-first authority model, Scene scoping, polyline LOS, fractional coordinates and separate future grid-registration benchmark intact.
- Version: `0.30.1-alpha.9`.

## 0.30.1-alpha.8 — Phase IV.30.1C: The Cartographer's Linework / Polyline Vision Barriers

- Adds backward-compatible authoritative polyline wall barriers: legacy two-point walls and doors remain valid, while wall paths may persist an ordered `points` collection.
- Extends sight-line resolution so every consecutive span of a polyline blocks vision exactly like an ordinary wall segment; doors deliberately remain two-point barriers.
- Updates Keeper rendering and roster presentation to draw/select one SVG polyline as one wall-path object while preserving existing manual segment workflows.
- Changes Living Contour review output from segment-budget compression to complete path suggestions, with up to 256 vertices per path and the existing 200-object review ceiling.
- Adds server-side path validation and a 6,000-point batch ceiling so richer geometry cannot turn one Assistant apply into unbounded work.
- Keeps Fine Contour Sampling, full-boundary tracing, topology guards, review-first authority, Behind-the-Curtain Scene scope and future grid-registration work intact.
- Version: `0.30.1-alpha.8`.

## 0.30.1-alpha.7 — Phase IV.30.1B.3A: Contour Topology Guard

- Guards Adaptive Contour Reduction against giant cross-room/cross-map replacement chords.
- Caps automatic cave simplification chords to six gameplay-grid units and rejects spans whose local deviation or travelled-path detour would distort the ordered contour.
- Forces perfectly straight but over-long contour runs to split at a midpoint, closing the zero-deviation loophole that could collapse an entire long boundary into one stroke.
- Keeps the 200-suggestion review ceiling, full-boundary budgeting, fractional endpoints and review-first authority unchanged.
- Adds regression coverage for local topology limits, detour/deviation guards, long-straight splitting and roadmap/version identity.
- Version: `0.30.1-alpha.7`.

## 0.30.1-alpha.6 — Phase IV.30.1B.3: The Cartographer's Economy / Adaptive Contour Reduction

- Replaces Living Contour's normal one-size-fits-all simplification tolerance with a whole-dungeon 200-segment budget allocator.
- Measures complete contour perimeters, suppresses tiny fine-mesh hatch/ink loops, reserves a minimum representation for retained boundaries, and distributes remaining fidelity by square-root perimeter weighting.
- Binary-searches a separate simplification tolerance for each contour so large cave walls receive useful detail without starving smaller pillars and islands.
- Retains the previous complete-boundary global simplifier only as a defensive fallback; raw scan-order truncation remains forbidden.
- Preserves Fine Contour Sampling, fractional endpoints, Keeper review, explicit Apply Selected authority, and the later misaligned-grid registration roadmap.
- Version: `0.30.1-alpha.6`.

## 0.30.1-alpha.5 — Phase IV.30.1B.2: Contour Simplification & Full-Boundary Tracing

- Traces complete connected Living Contour chains and closed cycles before the review budget is applied, so lower-map cave boundaries are no longer discarded merely because the scanner reached 200 fine strokes near the top first.
- Simplifies full contour paths with a bounded Douglas-Peucker-style pass, preserving meaningful cave bends while collapsing many tiny fine-mesh strokes into fewer authoritative LOS segments.
- Adapts simplification tolerance against the existing 200-suggestion server safety ceiling; every connected contour participates in each pass instead of using scan-order truncation.
- Fails closed on exceptionally fragmented artwork that still cannot fit the safe review budget rather than silently returning an incomplete top-of-map contour.
- Keeps Fine Contour Sampling, fractional barrier endpoints, Keeper review, explicit Apply Selected authority and the later misaligned-grid registration roadmap unchanged.
- Version: `0.30.1-alpha.5`.

## 0.30.1-alpha.4 — Phase IV.30.1B.1: Fine Contour Sampling

- Decouples Living Contour analysis resolution from the calibrated gameplay grid with an adaptive temporary subdivision mesh.
- Uses up to 6×6 analysis cells per gameplay square while reducing subdivision automatically to keep browser work bounded.
- Preserves fractional barrier endpoints so accepted cave contours can follow artwork more closely than whole-grid intersections.
- Keeps noise cleanup, the 200-suggestion cap, Keeper review and explicit Apply Selected authority unchanged.
- Leaves the hostile misaligned-grid benchmark for later grid-registration work rather than altering gameplay calibration.
- Version: `0.30.1-alpha.4`.

## 0.30.1-alpha.2 — Phase IV.30.1A: Curves & Continuity
## 0.30.1-alpha.3 — Phase IV.30.1B: The Living Contour

- Added a dedicated **Living Contour · caves** Cartography Assistant mode alongside Structural tracing.
- Classifies quiet playable floor against hatched/solid rock, traces their shared boundary, filters isolated noise and conservatively simplifies eligible cave corners into diagonal LOS segments.
- Preserves Keeper-only review, existing-barrier deduplication, the 200-suggestion safety cap and explicit Apply Selected authority boundary.
- Keeps the regular dungeon, advanced cave and hostile misaligned-grid maps as distinct Cartography benchmark tiers.


- Extends Structural Cartography beyond horizontal/vertical runs with 45-degree diagonal tracing passes for cave walls and angled architecture.
- Adds conservative continuity repair that can bridge one weak structural sample only when the local dark/quiet-side evidence still supports the same boundary, reducing tiny accidental breaks without blindly sealing clear openings.
- Keeps artwork tracing independent of exact grid-line placement, then simplifies accepted traces into the existing neighbouring-grid-intersection barrier vocabulary for authoritative Sight Beyond the Door geometry.
- Approximates curved/organic boundaries as short connected wall segments rather than forcing the analysis itself to be orthogonal.
- Preserves the IV.30 review-first contract, 200-suggestion cap, existing-barrier deduplication, and local-only artwork analysis.
- Records three benchmark tiers: regular orthogonal control, complex cave advanced benchmark, and the retained misaligned-grid hostile benchmark reserved for future grid-registration work.
- Version: `0.30.1-alpha.2`.

## 0.30.1-alpha.1 — Phase IV.30.1: Structural Cartography

- Adds a Structural tracing mode to the Keeper's Cartography Assistant for common thick-ink, cross-hatched dungeon maps.
- Builds a local dark-pixel mask and favours sustained boundaries whose opposite sides are quieter, reducing false positives from repetitive stone hatching.
- Traces horizontal and vertical architectural runs independently of exact grid-line placement, then converts useful runs back into existing calibrated grid barrier segments.
- Keeps the IV.30 review-first contract: structural results are a private draft, deduplicated against saved barriers, capped at 200, and never saved until explicitly applied.
- Records the retained cross-hatched dungeon map as the browser benchmark for this phase.
- Version: `0.30.1-alpha.1`.

## 0.30.0-alpha.1 — Phase IV.30: Keeper's Cartography Assistant

- Adds a Dungeon-Master-only Cartography Assistant inside Sight Beyond the Door.
- Analyses the currently loaded battlemap in the browser against the calibrated square grid; no map artwork is uploaded to an external analysis service.
- Suggests room/wall boundaries and conservative possible-door gaps as a private, dashed draft overlay.
- Adds Strong / Balanced / Fine detail passes plus a review checklist, Select All / Deselect All, Clear Draft and Apply Selected controls.
- Existing authoritative vision barriers are excluded from duplicate suggestions.
- Draft suggestions are never authoritative automatically: the Keeper must explicitly apply reviewed segments.
- Adds a server-authoritative batch barrier route capped at 200 reviewed suggestions, preserving DM membership and Scene scoping including Behind the Curtain preparation.
- Applies accepted barriers through the existing vision repository and refreshes Fog exploration once after the batch.
- Version: `0.30.0-alpha.1`.

## 0.29.5-alpha.2 — Phase IV.29D.1: Menagerie Filters
- Adds Keeper-only `All`, `On This Map`, and `Not On This Map` filters above the Bestiary register.
- Combines deployment filtering with the existing Bestiary text search rather than replacing it.
- Marks each creature record with Scene-aware deployment metadata and shows an `ON MAP · ×N` badge when one or more instances are deployed in the current live or privately prepared Scene.
- Keeps counts definition-based: `On This Map (4)` means four Bestiary creature records are represented on that Scene, while the card badge reports the number of deployed instances for that creature.
- Uses the already viewer-safe Scene token projection; no additional Player-facing catalogue or deployment state is exposed.

## 0.29.5-alpha.1 — Phase IV.29D: The Keeper's Menagerie

- Adds a source-agnostic BestiarySource boundary and composite Menagerie repository.
- Connects the first external source through a neutral Great Marketrealm Companion WordPress filter; Tabletop does not import Companion internals.
- Keeps the Training Grounds trio as the independent fallback shelf when Companion is unavailable.
- Companion records override matching stable IDs and future summons snapshot the currently published definition; deployed creatures are never silently rewritten.
- Completes the IV.29 Bestiary umbrella before the Keeper's Cartography Assistant.

## 0.29.4-alpha.1 — Phase IV.29C.1A: Roll for Damage

- Successful battlefield attacks now stop after the authoritative d20 result and expose a Guild Diceworks **Roll Damage** control to the acting Player or Keeper.
- The attack event becomes a server-owned single-use damage receipt; the browser submits only its opaque ID.
- Damage dice/formula, modifier, damage type, critical doubling, target, defenses, HP application and death consequences are resolved from authoritative server state.
- Misses never expose a damage roll and a damage receipt cannot be consumed twice.
- Damage must be rolled before the acting combatant ends its turn, preventing stale delayed damage.
- Existing direct AttackManager callers retain automatic damage resolution for backwards-compatible domain tests; the live AJAX battle flow opts into the explicit Diceworks step.

## 0.27.4-alpha.1 — IV.27E Light Wrought by Magic

## 0.29.3-alpha.1 — Phase IV.29C.1: Eyes on the Enemy

- Moves Attack, Dash, Disengage, Dodge and Help out of the brown Turn of Battle controls and into the combatant-owned surfaces.
- Player turns surface the authoritative combat dock inside the Adventurer's Satchel.
- Bestiary creature turns surface the same authoritative dock on the exact deployed creature instance, not merely its reusable definition.
- Highlights the active Bestiary card/instance and marks the Bestiary tab when one of the Keeper's creatures has the turn.
- Keeps End Turn and range/legality feedback in Turn of Battle.
- Lets selecting a visible battlefield token also select it as the current attack target.
- Reuses the existing AttackManager, BattleDeedManager, Arsenal, range and damage-defense machinery; no second combat rules engine is introduced.
- Keeps IV.29D — The Keeper's Menagerie next, before the Keeper's Cartography Assistant.

## 0.29.2-alpha.1 — Phase IV.29C: Creatures in Battle

- Provisions every newly summoned Bestiary creature with authoritative AC, HP, damage defenses and a complete Combat Arsenal.
- Converts each Bestiary attack into the existing Tabletop Arsenal/Combat/Damage model rather than adding parallel monster-combat rules.
- Uses the first Bestiary attack as the legacy Combat Profile/Damage Profile compatibility fallback.
- Keeps combat state instance-owned: later catalogue changes do not silently rewrite already-deployed creatures.
- Reuses existing initiative, conditions, targeting, range, vitality, defeated-state and Chronicle systems.
- Preserves hidden-creature privacy because combat projections are built only for viewer-authorized tokens.
- Records IV.29D — The Keeper's Menagerie as the next Bestiary expansion phase before the Cartography Assistant.

## 0.29.1-alpha.1 — Phase IV.29B: Summoned to the Table

- Enables Dungeon Master-only Bestiary deployment into the current live Scene or a privately prepared Atlas Scene.
- Supports manual map-click placement and Monster Deployment Threshold placement.
- Supports 1–12 copies per summon with grid-aware spreading and distinct Scene-owned token identities.
- Keeps Bestiary definitions separate from battlefield creature instances through the deployment service boundary.
- Supports visible or hidden creature tokens; existing player-state filtering remains authoritative.
- Pushes the Bestiary drawer tab down for clearer separation from the Keeper's Atlas tab.

- Adds server-authoritative magical world lights driven by Companion-certified spell illumination metadata.
- Shelfshine can be woven/quenched from the Satchel and obeys the Living Veil, walls and doors.
- Removes the viewer's Satchel immediately when their selected Companion token leaves the Chamber.


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

## 0.26.6-alpha.1 — Phase IV.26E: Chronicles of the Table

- Records every server-authoritative Quick Hands, Weapons to Hand, and Spell Pouch roll through one shared Chronicle recorder.
- Satchel rolls made during an Encounter join the existing Battle Chronicle with round/turn context.
- Satchel rolls made in Peace/Exploration persist in the new table-scoped Chamber Chronicle.
- Both Chronicles project the latest 12 entries and update through the existing Living Table heartbeat; no second poller was added.
- Chronicle records preserve the authoritative roll payload while presenting a safe human-readable summary.

## 0.26.5-alpha.2 — Phase IV.26D.2 Browser Corrective: Combatants May Leave

- Lets the Dungeon Master remove a Chamber token even while that token participates in the current Encounter.
- Removes the matching combatant from the authoritative Encounter before deleting the battlefield token.
- Keeps the active turn coherent when the current combatant leaves, and ends the Encounter automatically when its final combatant is removed.
- Adds model regression coverage for active and final-combatant removal.

## 0.26.5-alpha.1 — Phase IV.26D.2: Clear the Battlefield & The Living Gathering

- Adds server-authoritative **Remove from Chamber** token removal: DMs may remove any non-encounter token, while players may remove only their own Companion Character token.
- Protects active encounter integrity by refusing to remove tokens that are still registered combatants until the encounter ends.
- Extends the existing Living Table heartbeat to patch **Adventurers at the Table** from the authoritative member projection, so invitations, seats, removals, selected characters and Companion HP changes appear without manual page refresh.
- Adds an obvious **Choose Your Adventurer** callout whenever an active player has eligible Companion Characters but has not selected one for the Table.
- Keeps token removal non-destructive: Companion Characters, token recipes and Table membership are not deleted.

## IV.26D — Adventuring Measures at the Table

Current HP and Temporary HP can now be adjusted from the Satchel through an owner-scoped Companion write boundary. Maximum HP remains Companion-certified and read-only.

## 0.26.3-alpha.2 — Phase IV.26C.3: Magic at Your Fingertips

- Makes eligible Companion-projected Spell Pouch entries actionable from the Adventurer’s Satchel.
- Spell attacks roll against the authoritative projected spell attack modifier; damage and healing use the authoritative projected formula and roll modifier.
- Adds distinct Attack, Damage and Healing actions while keeping save resolution and battlefield targeting for a later combat integration.
- Adds an authenticated `gmrt_spell_pouch_roll` boundary that accepts only spell/action identifiers and re-resolves the seated Companion Character server-side.
- Keeps natural 20/1 spell-attack behaviour as presentation flourishes rather than inventing new automatic outcomes.


## 0.26.2-alpha.1 — Phase IV.26B: Weapons to Hand
- Adds equipped Companion weapon cards to the Adventurer's Satchel.
- Adds server-authoritative attack and damage rolls using only Companion-projected attack IDs.
- Keeps attack bonuses, damage formulae and modifiers off the browser trust boundary.
## 0.24.0-alpha.1 — Phase IV.24: Peace and Battle

- Added Exploration Mode as the natural state of an active Scene when no current Encounter exists.
- Added a Dungeon Master Start Encounter workflow with explicit combatant selection and initiative entry.
- Added Dungeon Master End Encounter controls that retire battle without mutating Scene tokens, Fog/exploration, grid calibration or Vision Layer state.
- Added a single-operation server-authoritative Encounter begin service so partial preparation cannot strand the Table in battle setup.
- Replaced encounter-lifecycle page reloads with live Chamber markup replacement and JavaScript re-binding for connected DM/Player browsers.
- Kept movement, Living Veil, remembered exploration, walls and doors alive during Exploration Mode.


## 0.23.1-alpha.2 — Phase IV.23A corrective: Reliable Summons & Seat Removal

- Generate invitation links with the canonical Tabletop query route so they work without relying on rewrite rules being flushed.
- Use player-facing “Sign in to the Marketrealm Companion” wording in invitation email.
- Allow the active Dungeon Master to remove invited or active players from a Table so they can be reinvited cleanly later.
- Removed/left members are omitted from the live Gathering roster while their canonical membership history remains persisted.


## 0.23.0-alpha.1 — Phase IV.23: The Gathering at the Table

- Replaces numeric Table member placeholders with projected WordPress display names and avatars.
- Adds Dungeon Master invitations for existing WordPress users by username, email or user ID.
- Adds an explicit invited-player `Take My Seat` acceptance flow before Chamber access becomes active.
- Keeps identity, persistent Table membership and Companion character assignment as separate concerns.
- Surfaces GMRC availability/version through the existing isolated Companion integration boundary.
- Preserves server-authoritative membership changes and opaque Companion character references.
# Changelog

## 0.31.0-alpha.1 — Phase IV.31: The Keeper's Lantern Rack
- Adds DM-owned Scene lighting with Torch, Lantern, Brazier, Candle and Magical Light presets.
- Reuses Living Veil LOS/illumination, including wall blocking and player-safe projection.
- Supports Behind-the-Curtain preparation plus douse/remove controls.


## 0.22.2-alpha.2 — Phase IV.22B.1: The Keeper's Cartography

- Added continuous grid-snapped wall tracing for faster room and corridor authoring.
- Added a live snapped barrier preview while choosing the next wall/door endpoint.
- Added vision-segment selection/highlighting and one-click Undo Last.
- Improved closed-door guidance so DMs know to frame doorway edges with walls.
- Kept vision persistence, LOS resolution, exploration memory, and player filtering server-authoritative.

## 0.22.2-alpha.1 — Phase IV.22B: Sight Beyond the Door

- Added a persisted Dungeon Master vision layer with grid-snapped wall and door segments.
- Walls and closed doors now block server-projected Fog of War sight; open doors permit sight through their segment.
- Kept vision blockers server-authoritative for current visibility and player token filtering; players do not receive DM vision-layer geometry.
- Added DM controls to draw walls, place doors, toggle doors open/closed, and remove vision barriers.
- Exploration memory now accumulates from blocker-aware visibility, allowing travelled corridors and visited rooms to remain as the party's dim remembered route.
- Disabled the browser's circular vision reconstruction whenever persisted blockers exist so client presentation cannot see through server-authored walls.
- Added regression coverage for barrier models, persistence, line intersection, Fog integration, DM-only geometry exposure, and AJAX/UI wiring.

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

## 0.23.1-alpha.1 — Phase IV.23A: The Summons to the Table
- Table invitations remain canonical membership records even when mail delivery fails.
- Added WordPress email summons with a direct Table link and explicit configured admin-email sender.
- Added `gmrt_table_invitation_created` integration event for Companion/in-app notification consumers.
- Added `gmrt_table_member_avatar_url` identity filter so Companion profile imagery can override the WordPress avatar without coupling domains.
- Reworked Gathering seats to communicate DM/Player role with distinct accents and a separate presence pip instead of administrative Active text.

### 0.23.2-alpha.1 — Phase IV.23A.1: The Table Keeps Time
- Extended the existing five-second chamber heartbeat to detect authoritative Encounter ID/revision changes.
- Connected players now automatically re-render their chamber when another participant advances the Encounter, keeping round and active-turn presentation current without manual refresh.
- Added a server-derived shared-state revision covering Table membership, scene/tokens, encounter, vitality, conditions, Chronicle, Fog/exploration and the Vision Layer as a reusable synchronization seam for future Exploration/Encounter mode.

### 0.23.3-alpha.1 — Phase IV.23A.2: The Living Table

- Replaces full chamber reloads for ordinary remote encounter turn/round revisions with in-place DOM updates.
- Live-patches the round label, current combatant label and active battlefield token from the authoritative heartbeat.
- Keeps the existing five-second server-authoritative polling path for tokens, Fog, Vision Layer and Battle Chronicle.
- Retains a safe reload fallback only when the encounter lifecycle itself changes; IV.24 will replace that with Exploration/Encounter transitions.
- Adds an accessible live-status announcement when the remote turn changes.

- IV.24 alpha.2 corrective: live Exploration/Encounter transitions now fetch viewer-specific Chamber markup through authenticated AJAX and patch only lifecycle/Chronicle regions, preserving the battlefield DOM and preventing cached DM markup from leaking into Player presentation.

### 0.24.0-alpha.3 — Peace and Battle live-state repair
- Restores the persistent lifecycle and Battle Chronicle DOM anchors required by the Living Table heartbeat.
- Player Exploration/Encounter transitions patch only live lifecycle regions, keeping the battlefield and Fog heartbeat alive.
- DM Begin Battle / End Encounter rebuild through the authenticated Tabletop fragment endpoint so newly rendered combat controls receive complete bindings.
- Removes the cached frontend-page fetch from live Chamber rebuilding.

## 0.25.0-alpha.1 — Phase IV.25: The Companion Character Gate
- Active Table members can choose only Companion Characters owned by their WordPress account.
- Character ownership is revalidated server-side before the Table seat is assigned.
- The selected Character creates a player-controlled CHARACTER token on the active Scene when one is not already present.
- Forged Companion token imagery, focus and zoom are projected onto the battlefield token.
- The Chamber shows the selected Character and owner-scoped Character picker.


## 0.25.0-alpha.2 — Phase IV.25.1: The Character Bears Their Token

- Allows the Chamber to render Companion-generated SVG portrait fallbacks as narrowly validated image data URIs instead of losing the `data:` source through WordPress `esc_url()`.
- Keeps ordinary uploaded Tabletop Token URLs on the normal WordPress URL-escaping path.
- Preserves the already-certified Character Gate ownership, movement, invitation, Fog, and seat-assignment behaviour.


## 0.25.0-alpha.3 — Phase IV.25.2: The Keeper Keeps Pace

- Keeps the Keeper's local Encounter revision stale until the authoritative Living Table refresh arrives after End Turn.
- Lets the DM consume the same in-place round/current-combatant/active-token patch already proven on Player browsers.
- Restores the End Turn control after a successful refresh instead of leaving it disabled as “Passing…”.
- Companion-side owner-aware portrait projection accompanies this corrective so a DM sees the seated player's actual forged/custom token artwork rather than a viewer-scoped generated fallback.


### Phase IV.26 — The Adventurer's Satchel (0.26.0-alpha.1)
- Tabletop support for the owner-scoped tabletop play projection and pull-out Adventurer's Satchel.
- Companion remains authoritative for character mechanics; Tabletop consumes the projection without duplicating character persistence.


### 0.26.0-alpha.2 — Phase IV.26 Browser Corrective
- Fixes the Adventurer's Satchel header artwork renderer to use the supported `CompanionTokenImageSource::escaped()` API.
- Prevents the Player Chamber fatal caused by the nonexistent `CompanionTokenImageSource::sanitize()` call.
- Adds regression coverage that forbids the unsupported Satchel image-source call from returning.


### Phase IV.26A — Quick Hands at the Table (0.26.1-alpha.1)
- Turns Satchel abilities, saving throws, skills and Initiative into accessible d20 roll controls.
- Resolves the seated Companion Character and modifier server-side; the browser never supplies its own modifier.
- Uses the existing cryptographically secure d20 roller and reports natural 20/1 as presentation flourishes rather than automatic check/save outcomes.
- Works from the Satchel in both Peace and Battle modes.

## 0.26.3-alpha.1 — IV.26C The Spell Pouch
- Unfurls the Adventurer's Satchel to the available viewport height with internal scrolling and a sticky identity header.
- Adds a Companion-authoritative Spell Pouch with casting measures, slot summary and learned spell cards.

## 0.26.4-alpha.2 — IV.26D.1 One Measure of the Adventurer
- Widens the Adventurer's Satchel and keeps its tall internal-scroll presentation without horizontal overflow.
- Projects each seated Companion character into the Gathering through the existing owner-scoped gateway.
- Makes Adventurers at the Table prefer Companion-authoritative current/maximum/temporary HP over stale local token vitality.
- Mirrors successful Satchel HP edits into the current player's Gathering card immediately.


## 0.26.8-alpha.1 — Phase IV.26G: Footsteps Through the Veil
- Records a bounded six-step trail for player-controlled character movement.
- Projects footsteps server-side per viewer so fog never leaks hidden movement history.
- Own recent steps may linger faintly in explored memory; other players' steps require current visibility.
- Reuses Fellowship colours and the Living Table heartbeat.

### 0.29.3-alpha.2 — Eyes on the Enemy browser corrective
- Bridge Companion-certified equipped weapon attacks into the active character token Combat Arsenal for authoritative Player battlefield targeting and attacks.
- Make denied Guild Diceworks rolls visibly halt rather than appearing permanently in-progress.
- Clarify Turn of Battle target status and add conspicuous Player-turn beacons to the Satchel and shared combat guidance.

## 0.30.1-alpha.13 — Phase IV.30.1F.1: The Surveyor Learns the Difference Between a Grid and a Wall

- Corrects Printed Grid registration after the hostile benchmark showed strong room walls could outscore the faint baked-in artwork grid.
- Treats thick dark architectural ink as negative registration evidence and favours thin pale strokes that recover quickly into quiet floor.
- Requires repeated comb evidence on both axes so sparse room dimensions cannot masquerade as a square grid.
- Resolves strongly supported 2–6x room-size harmonics back toward the smaller fundamental printed-grid spacing instead of simply choosing the strongest large rectangle cadence.
- Keeps detection browser-local and preview-only; the existing Save Grid action remains the sole authoritative persistence boundary.

## 0.31.1-alpha.1 — Phase IV.31.1: The Keeper Strikes a Match
- Places every Keeper environmental source lit by default and keeps douse/relight as a reversible persisted state rather than removal.
- Shows Lit/Doused state and the source radius directly in the Lantern Rack roster.
- Certifies bright-light radii of Torch 20 ft, Lantern 30 ft, Brazier 60 ft, Candle 10 ft, and Magical Light 40 ft, each with an equal dim-light band.
- Sizes environmental battlefield glows from authoritative projected range plus calibrated grid geometry so every source visibly represents its illumination footprint.
- Keeps Living Veil projection, Scene ownership, wall occlusion and Behind-the-Curtain preparation authoritative.

## Phase IV.32.3 — Adventurers in Miniature
- Extended the Tabletop pixel language into live-play party, encounter, Satchel, Bestiary and active-token presentation.
- Added current-turn highlighting to Gathering character seats by matching the existing authoritative active token source to the seated Companion character on both initial render and Living Table refresh.
- Kept initiative, turn advancement, combat resolution, HP, conditions, targeting, movement, Fog and persistence unchanged.

## 0.32.3-alpha.3 — Phase IV.32.3B: The Keeper Rolls Behind the Screen
- Adds a Dungeon-Master-only Secret d20 beneath the Keeper's Gathering seat.
- Uses the existing `SecureD20Roller` behind a server-side active-DM membership gate.
- Keeps the result response-only in the Keeper browser: no Chamber Chronicle, Battle Chronicle or Living Table projection.
- Preserves the last private result across normal live Gathering redraws without server persistence.

## 0.32.4-alpha.1 — Phase IV.32.4A: The Battlefield Finds Its Pixels
- Carries the Tabletop's 16-bit presentation language onto the battlefield grid and miniatures without changing calibrated geometry or token coordinates.
- Gives selected tokens a four-corner focus reticle and refines the certified current-turn cursor into a stepped pixel frame.
- Pixel-styles the existing authoritative target line/range plaque, Keeper deployment thresholds, Vision door editing marks and Footsteps movement trail.
- Keeps movement, targeting, Vision, Fog, encounter state and persistence untouched and preserves reduced-motion behaviour.

### 0.32.4-alpha.1 — IV.32.4A browser-certification polish
- Lets the five combat deed buttons wrap responsively when a combat dock is narrower than their readable pixel labels.
- Keeps wide docks on one row, preserves whole labels such as `Disengage`, and typically settles narrow docks into a balanced 3 + 2 arrangement without shrinking typography or changing deed behaviour.

- IV.32.4A final polish: reflowed Guild Diceworks attack certification inside narrow combat docks and clear transient attack/damage results when the authoritative Encounter turn identity changes, preserving Chronicle history.
- IV.32.4D — The Battlefield's Final Inspection: established the final presentation-only battlefield layer stack so the IV.32.4 pixel grid, trails, Vision, Keeper lights, Living Veil, thresholds, miniatures and targeting feedback compose predictably without changing geometry or authority.

## Phase IV.32.5 — Pippin Discovers Pixel Art

- Gives generated Dungeon, Forest and Village scenery a crisp SNES-era projection while preserving the existing deterministic Scene Forge plans.
- Adds dungeon tile highlight/shadow pixels, block-canopy trees, faceted rocks, stepped village architecture, pixel logs, octagonal wells, garden posts and square trail treatment.
- Keeps Scene topology, floor membership, LOS barriers, doors, Keeper lights, grid registration, Fog and encounter authority unchanged.
- Adds regression coverage for the pixel projection and the Forge authority boundary.

## 0.32.5-alpha.1 — IV.32.5A: Pippin Fixes the Drawer Hinges
- Makes Atlas and Bestiary tab opening lifecycle-safe by delegating their click handling from the stable document, so replacing the Chamber while entering/leaving generated Scenes cannot strand fresh drawer tabs without handlers.
- Keeps the existing moving Keeper rail, mutual drawer exclusion, accessibility state and Bestiary search focus behaviour intact.
- Unslashes incoming Scene names before WordPress sanitisation so apostrophes render as `Pippin's` rather than leaking the request escape character into persisted/generated Scene titles.

### Phase IV.32.6 — The Door to the Table
- Replaced the signed-out plain notice with a dedicated pixel-styled Tabletop entrance.
- Added a WordPress-owned **Enter the Tabletop** login action that returns visitors to the exact requested Table/invitation URL after authentication.
- Reused packaged Pippin artwork for the scenic threshold and field-note treatment without adding a second authentication system.
- Added responsive, keyboard-focus and reduced-motion presentation safeguards.
- Added regression coverage for the signed-out Door, preserved return URL and WordPress authentication authority.


## 0.32.6-alpha.2 — Phase IV.32.6A: The Door Has a Lock
- Replaces the external `wp-login.php` hand-off with a Tabletop-native front-end sign-in form while continuing to use WordPress `wp_signon()` and auth cookies as the sole authentication authority.
- Processes login during `template_redirect` so auth cookies are issued before page output, protects the form with a dedicated nonce, supports username/email, password-manager autocomplete and Remember Me, and returns successful sign-ins to the exact requested Table/invitation URL.
- Promotes packaged Pippin dungeon artwork to a full-bleed cinematic threshold behind the login panel.
- Gives the authenticated `Your chair is waiting` invitation state the same full-bleed threshold presentation while retaining explicit `Take My Seat` membership acceptance as a separate action.
- Adds accessible inline login errors, keyboard focus, responsive composition and reduced-motion safeguards.

## Phase IV.33.1 — The Keeper Names the Campaign

- Adds a DM-authorised Campaign Shelf when the Tabletop is opened without a selected Table.
- Adds persistent Keeper-created Tables with campaign name and optional description.
- New Tables automatically seat their creator as Dungeon Master, become active, and open with a blank generated Scene named `The First Blank Page`.
- Existing Table records remain compatible when no description metadata exists.
- Keeps Sage's Combat Testing Grounds as a secondary development fixture rather than using it as the real campaign creation path.
- Adds a Companion-friendly `gmrt_tabletop_may_create_table` policy seam while preserving server-side nonce and role enforcement.

### Phase IV.33.2 — The Keeper Opens the Doors
- Added Campaign Shelf roster projection and DM-side player administration.
- Keepers can summon and remove players without first entering the battlefield.
- Reused the existing authoritative Gathering invitation/removal endpoints and membership persistence.

### Phase IV.33.3 — Pippin Remembers the Way
- Turned the bare Tabletop route into Pippin's persistent Table Atlas for both DMs and Players.
- Added role-aware Return to Table / Take My Seat campaign cards over the established Pippin cartographer artwork.
- Added Keeper-only permanent Tabletop removal with nonce and owner authorization plus campaign-keyed persistence cleanup.

- IV.33.3 final browser polish: Pippin's Table Atlas now suppresses the in-Chamber masthead, and permanent Table removal can resolve legacy Testing Grounds records whose stored option key predates their authoritative Table UUID.

- IV.33.3 final polish: successful Tabletop removal now updates Pippin's Table Atlas immediately in the browser, removing the deleted campaign card without requiring a manual page refresh; removing the final card restores the Atlas empty-state message.

### Phase IV.33.4 — The First Map on the Table
- Adds a three-way opening-Scene choice to Keeper campaign creation: Blank Table, owned uploaded Atlas map, or Pippin's Scene Forge.
- Preserves `The First Blank Page` as the safe default and creates `Pippin's Forge Workbench` for Forge-first campaigns.
- Copies reusable image-backed Atlas Scenes into the new Table without moving the source, preserving map attachment, dimensions and calibrated grid.
- Enforces source-Table ownership server-side and deliberately excludes generated Forge Scenes from the Atlas-copy picker because their authoritative generated-world state is stored beyond the bare Scene surface.
- Carries Forge-first creation into the new campaign and opens the existing Scene Forge automatically, without adding a second Forge workflow.
- Adds regression coverage for all three creation paths, Atlas ownership/cloning and Forge onboarding.


## Phase IV.34.1 — The Keeper Calls the Session
- Added persistent numbered Table Sessions with optional titles, start/end timestamps and active/ended lifecycle.
- Added Keeper-only, nonce-protected Session start/end actions without changing campaign persistence or campaign status.
- Projected the active Session through the Tabletop chamber/Living Table state so refreshes and other seated viewers follow the same Session lifecycle.
- Added command-header Session controls/status and live lifecycle refresh handling.
- Campaign removal now also purges Session records.
