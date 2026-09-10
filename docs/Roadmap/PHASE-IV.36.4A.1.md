# Phase IV.36.4A.1 — The Satchel Reads the New Signpost

IV.36.4A correctly introduced a Player-safe integration projection so hidden
Forge secrets and traps no longer cross the live AJAX boundary. An older Satchel
regression still required the literal pre-filter expression
`'integrations' => $state->integrations()`.

That assertion was checking an implementation signpost rather than the Satchel
contract. The live state still exposes the Companion projection through the
filtered `$integrations` payload; only `dungeon_forge` is replaced with its
Player-safe projection.

This correction updates the regression to certify the new boundary without
weakening either Satchel cleanup or Keeper secrecy.

Expected suite remains 1,152 tests.
