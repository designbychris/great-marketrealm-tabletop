# Player Language Preference

Tabletop does not maintain a second language preference. It honours the Companion-owned WordPress user meta key `gmrc_interface_locale` through WordPress's `determine_locale` filter before the Tabletop text domain is loaded.

Supported values in the first multilingual milestone are `en_GB` and `nl_NL`; an empty or unavailable value falls back to WordPress's normal site/default locale.

This makes language personal to each participant rather than a Table or Campaign setting.
