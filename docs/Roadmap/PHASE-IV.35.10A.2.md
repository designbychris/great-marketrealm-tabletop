# Phase IV.35.10A.2 — The Old Test Reads the New Signpost

The IV.29C regression still asserted the original implementation detail that
Bestiary arsenal attacks crossed directly through `AttackKind::assert()`.

IV.35.10A deliberately replaced that direct assertion with the new
`BestiaryCompatibilityNormalizer` boundary so neutral Companion attack labels
can be translated before they enter Tabletop combat state.

This hotfix updates the older regression to protect the current architectural
contract instead:

- every Bestiary attack still becomes a normal `ArsenalAttack`;
- the attack kind must now pass through
  `$this->compatibility->attackKind(...)`;
- the existing Combat Arsenal and Bestiary source-reference assertions remain
  unchanged.

No runtime behaviour changes in this hotfix.
