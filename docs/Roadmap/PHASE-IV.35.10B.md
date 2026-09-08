# Phase IV.35.10B — The Boss Would Like to Participate

Browser certification of IV.35.10 exposed three integration seams:

1. The Keeper's Bestiary only received Companion creatures that also had enough
   Companion-specific initiative metadata to be encounter-ready there.
2. Qualified Bestiary defenses such as `Slashing (unless fire is used)` were
   handed directly to Tabletop's canonical damage-defense model.
3. Tokens created during a live Forge refresh were rendered after the original
   token interaction listeners had already been attached.

This correction keeps the plugin boundary explicit:

- Companion publishes a Tabletop-ready creature when it has the concrete AC/HP
  needed by Tabletop, without requiring a Dexterity score merely to appear on
  the Keeper's shelf.
- Tabletop translates representable defense vocabulary at the Bestiary boundary
  before creating `DamageDefenseProfile`; descriptive defense entries that are
  not concrete damage types remain Bestiary metadata rather than aborting the
  whole creature deployment.
- Live-created tokens receive the same selection, drag and keyboard bindings as
  tokens present on first render. Hidden-from-Players remains a visibility
  property, not a Keeper interaction lock.

Reference-only creatures with no AC/HP remain excluded from deployment rather
than receiving invented statistics.
