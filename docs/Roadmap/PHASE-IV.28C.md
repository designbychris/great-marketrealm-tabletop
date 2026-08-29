# Phase IV.28C — Passage Between Places

When the Keeper opens a Scene from the Atlas, the active Scene remains server-authoritative. Connected live viewers detect that Scene identity change through the existing Living Table heartbeat and replace the Chamber through the authenticated fragment endpoint rather than reloading the browser page.

The replacement rehydrates the destination Scene's map, grid, tokens, Fog, vision barriers, lights, encounter projection, Chronicle and other Scene-bound presentation from authoritative state. No second polling loop is introduced.

Dungeon Masters working Behind the Curtain remain pinned to their explicitly selected private preparation Scene. Opening or changing the live Scene elsewhere does not pull that private preparation projection away. Returning to the live Scene resolves whichever Scene is authoritative at that moment.

Tokens remain Scene-owned. Passage changes the connected viewer's live battlefield projection; it does not destructively transfer a token out of another Scene or erase that Scene's remembered token layout.
