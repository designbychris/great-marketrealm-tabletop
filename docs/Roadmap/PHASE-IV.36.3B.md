# Phase IV.36.3B — The Empty Chair Is Not the Dungeon Master

The IV.36.3 Player secret-projection boundary assumed a populated Tabletop state.
The chamber can legitimately render with a null state while no Table is currently
resolved, so dereferencing `isDungeonMaster()` there caused a WordPress fatal error.

The secret filter now runs only when a Tabletop state exists. A regression locks the
nullable presentation boundary.

Expected suite: 1,141 tests.
