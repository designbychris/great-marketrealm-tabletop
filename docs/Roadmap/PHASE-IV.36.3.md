# Phase IV.36.3 — The Dungeon Has Secrets

The Dungeon Forge can optionally prepare deterministic Keeper-only secrets for Dungeon Scenes.

The first secret vocabulary is deliberately narrow: a concealed door and a hidden cache. Unrevealed secrets remain Keeper-only server-side presentation data. A concealed Forge door is omitted from the Player SVG projection so it reads as uninterrupted wall until the Keeper reveals it. Hidden caches likewise do not cross the Player presentation boundary before reveal.

The Keeper sees secret markers and explicitly reveals them. Revelation persists in the Forge projection and becomes visible to Players on their next/live page refresh. This establishes the discovery/reveal boundary without implementing trap mechanics; traps belong to IV.36.4.
