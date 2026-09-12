# Dutch language pack (`nl_NL`)

The Tabletop's first production language pack targets **Dutch (Netherlands)** using the WordPress locale `nl_NL`.

Files:

- `languages/great-marketrealm-tabletop-nl_NL.po` — editable translator catalogue.
- `languages/great-marketrealm-tabletop-nl_NL.mo` — compiled catalogue loaded by WordPress.

## Scope

This pack translates the strings exposed by the current Interface Translation Audit, including the Session Desk, Keeper Tools, Atlas, Bestiary, scene/session controls, invitation/campaign controls, status/error messages and the JavaScript-facing translation catalogue.

Canonical authored MarketRealm content remains separate. Bestiary lore, creature names, spells, items, classes, races and setting prose are not automatically translated by the UI pack. Proper names such as Auby, Pippin Peppercorn and MarketRealm remain proper names.

## Adding another locale

Do not add locale branches to PHP or JavaScript. Copy the PO catalogue to the appropriate WordPress locale name, translate `msgstr` values, compile a matching MO file, and keep using the same `great-marketrealm-tabletop` text domain.
