# Internationalisation & Language Packs

## Principle

The Tabletop is authored in English but must accept any WordPress locale pack. Locale support must not alter encounter, scene, token or persistence behaviour.

## Two translation layers

### 1. Interface translation — WordPress gettext

Use the `great-marketrealm-tabletop` text domain for application chrome:

- Keeper rail and drawer labels;
- session, encounter and scene controls;
- Atlas/Bestiary interface copy;
- movement, fog, lighting and object controls;
- status messages, validation and accessibility labels.

PHP interface strings should use WordPress i18n helpers with the literal Tabletop text domain.

### 2. Canonical content translation — curated catalogue (future phase)

Bestiary descriptions and imported MarketRealm rules/lore are authored content. They should eventually use stable content identifiers and curated locale values rather than automatic gettext replacement of English prose.

Missing content translations fall back to canonical English while the surrounding UI can still be fully localized.

## Locale independence

Do not add Dutch-specific (or any locale-specific) branches to gameplay code. A language pack is data and the same implementation must support every WordPress locale.

## JavaScript

The Tabletop has substantial browser-side UI copy. It will be migrated to `wp.i18n` incrementally during the interface-audit phase, with script handles declaring the `wp-i18n` dependency and using `wp_set_script_translations()` where appropriate.

## Translation extraction

See `languages/README.md` for the WP-CLI extraction command and file naming conventions.

## JavaScript interface strings

The live Tabletop browser receives interface translations through `gmrtTabletop.strings`. The extractable PHP catalogue lives in `TabletopI18n::strings()`. New browser status, confirmation, button-state, error and accessibility copy should be added there instead of being introduced as an English-only JavaScript literal.
