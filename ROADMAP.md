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

Persistent token identity and placement on the active Scene.
