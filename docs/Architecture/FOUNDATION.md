# Tabletop Foundation Architecture

Great Marketrealm Companion owns persistent RPG identity and certified rules-facing records.

Great Marketrealm Tabletop owns live table/session state.

All Companion integration belongs beneath `app/Integration/Companion/`.

The plugin contains its own small PSR-4 source loader so production boot does not require a committed `vendor/` directory. Composer remains the development dependency manager and supplies PHPUnit.

Activation seeds `gmrt_active_table_capacity` to `2`. Enforcement belongs to later Table Registry and Steward's Table Rules phases.
