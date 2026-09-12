# Great Marketrealm Tabletop language packs

English is the canonical source language for the Tabletop interface.

WordPress gettext catalogues for this plugin use the text domain:

`great-marketrealm-tabletop`

## File names

Compiled locale packs should follow WordPress conventions, for example:

- `great-marketrealm-tabletop-nl_NL.po` / `.mo`
- `great-marketrealm-tabletop-de_DE.po` / `.mo`
- `great-marketrealm-tabletop-fr_FR.po` / `.mo`
- `great-marketrealm-tabletop-es_ES.po` / `.mo`

JavaScript catalogues may be added later using WordPress JSON translation files when a script has been migrated to `wp.i18n`.

## Interface vs MarketRealm content

This directory is for **application interface** language: Keeper controls, drawer labels, scene/encounter controls, status messages and accessibility text.

Bestiary lore, spell/item/race/class text and other authored MarketRealm material remain **canonical content** and will use a separate curated content-translation pipeline. This avoids blindly translating proper nouns and setting wordplay.

## Generating a POT catalogue

From the plugin root, with WP-CLI's i18n command available:

```bash
wp i18n make-pot . languages/great-marketrealm-tabletop.pot --domain=great-marketrealm-tabletop --exclude=vendor,node_modules
```
