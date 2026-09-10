# Phase IV.36.5 — There May Be Treasure

The Dungeon Forge can now prepare hidden treasure alongside its inhabitants, secrets and traps.

## Scope

- Both Forge surfaces gain an optional **Include treasure** switch for Dungeon scenes.
- `ForgeTreasurePlanner` deterministically places a sparse set of treasure records in suitable rooms.
- A genuine Boss Lair receives a **Boss Hoard** before ordinary cache placement.
- Treasure begins Keeper-only, unrevealed and unlooted.
- The Player presentation and live AJAX boundaries expose only revealed treasure.
- Dungeon Master Controls gain **The Keeper's Treasure Ledger** for manual treasure preparation.
- The Keeper may add, edit, move, reveal, conceal, mark looted, reset or remove treasure.
- Treasure markers use the same live Chamber replacement/revision flow as recent Forge secrets and traps.
- Manual treasure supports Coin Cache, Trade Goods, Adventurer's Cache, Curio Stash and Boss Hoard categories with editable Keeper notes.

## Deliberate boundary

This phase does **not** create a second inventory, coin purse, magic-item repository or currency-transfer system inside Cartography. Treasure is prepared adventure-state metadata. A later bridge can hand claimed treasure to canonical Companion character/fellowship inventory and currency systems without duplicating ownership rules in the Tabletop.

Pippin's field note: *“I have marked the treasure with an X. Unfortunately I have also learned what usually lives underneath an X.”*
