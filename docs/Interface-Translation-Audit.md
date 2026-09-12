# Interface Translation Audit

The Tabletop now treats browser and PHP interface language as translatable UI while keeping Bestiary names, adventure prose and other authored MarketRealm content separate.

## Translation-ready in this pass

- Keeper Session / Tools / Atlas / Bestiary rail and primary drawer headings.
- Session lifecycle, recap and workspace accessibility labels.
- Core battlemap, Veil and Lantern Rack controls.
- Live JavaScript status/error copy for refresh, traps, treasure, furniture, Mimics, campaign creation/linking, invitations and Session lifecycle.
- JavaScript translations are catalogued in PHP by `TabletopI18n` and passed to the browser as `gmrtTabletop.strings`.

## Deliberately separate

Creature names, adventure/story text, rules descriptions and other authored MarketRealm material remain canonical content. These need curated content translation rather than generic interface gettext.

## Rule for new work

New PHP interface strings use `great-marketrealm-tabletop`. New JavaScript-visible language belongs in `TabletopI18n::strings()` (or a future `wp.i18n` catalogue), not as an English-only browser literal.
