# Phase IV.36.2A — The Repository Was on the Wrong Shelf

IV.36.2 referenced `BestiaryRepositoryFactory` from the service provider without importing its actual namespace.

This corrective pass imports:

`GreatMarketrealmTabletop\Tabletop\Bestiary\Services\BestiaryRepositoryFactory`

No population semantics, encounter behaviour, or test expectations change. The IV.36.2 suite remains 1,133 tests.
