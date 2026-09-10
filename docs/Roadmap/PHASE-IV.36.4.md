# Phase IV.36.4 — Pippin Steps on Something

The Dungeon Forge can now prepare deterministic hidden traps without creating a
second combat system.

- Dungeon-only, opt-in **Include traps** control on both Forge surfaces.
- Initial vocabulary: Pressure Plate and Tripwire.
- Traps begin hidden, armed and unsprung; Boss Lairs are excluded from ordinary
  pressure-plate placement.
- Player presentation strips unrevealed traps server-side.
- Player token movement checks the complete movement segment, so crossing a trigger
  can spring it even when the final token position lands beyond the marker.
- A sprung trap becomes revealed, disarmed and persistently triggered.
- Keeper movement never accidentally springs preparation traps.
- Keeper controls: Reveal, Disarm, Spring and Reset.
- This phase establishes trap lifecycle and discovery. Mechanical damage/effects are
  deliberately not invented here; later trap vocabulary can route effects through
  existing vitality/condition rules.

Expected suite: 1,148 tests.
