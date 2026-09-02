# Phase IV.32.3B — The Keeper Rolls Behind the Screen

A Dungeon Master may make a private d20 check from their own Gathering seat.

The roll uses the existing `SecureD20Roller` and is authorised server-side against the active Dungeon Master membership. The result is returned only to the requesting Keeper browser. It is deliberately not persisted to the Chamber Chronicle, Battle Chronicle, Living Table state, Encounter state, or any shared dice history.

The browser keeps the most recent secret result only in local memory so a normal Gathering live redraw does not immediately erase it. Players receive neither the control nor the result, and the AJAX endpoint rejects any non-DM caller even if they attempt to invoke it manually.

This phase does not change public Guild Diceworks, initiative, encounter authority, Chronicle routing, Fog, movement, targeting, damage, HP, or conditions.
