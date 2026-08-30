# Phase IV.29D.1 — Menagerie Filters

The Keeper's growing Menagerie now has a Scene-aware filter rail above the Bestiary register. **All**, **On This Map**, and **Not On This Map** combine with the existing text search so the Dungeon Master can quickly isolate creatures already in use or those still on the shelf.

A creature record is considered **On This Map** when the current live Scene, or the privately prepared Scene being viewed Behind the Curtain, contains at least one Scene-owned token whose Bestiary source reference matches that definition. The filter count is definition-based, while each matching card carries an `ON MAP · ×N` badge showing the number of deployed instances.

The feature consumes the existing Dungeon-Master Scene token projection only. It creates no parallel deployment registry and exposes no Bestiary catalogue or hidden creature state to Players.
