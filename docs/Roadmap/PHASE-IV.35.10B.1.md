# Phase IV.35.10B.1 — The Hydra Reads Left to Right

The Bestiary compatibility boundary correctly recognised every canonical
damage-type word inside a qualified defense label, but it returned the first
damage type in Tabletop's canonical enumeration rather than the first damage
type actually written by the source.

That made:

`Slashing (unless fire is used)`

resolve to `fire`, because Fire appears before Slashing in the canonical
DamageType list.

This correction keeps qualified neutral Bestiary text supported while choosing
the earliest canonical damage type in the source string. The Kale Hydra
therefore crosses the boundary as `slashing`, while ordinary exact damage
types and existing aliases remain unchanged.

No new regression test is required: the IV.35.10B regression that exposed this
ordering bug already protects the intended behaviour.
