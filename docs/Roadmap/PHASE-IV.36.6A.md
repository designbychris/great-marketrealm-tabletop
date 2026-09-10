# Phase IV.36.6A — Pippin Filed the Story One Shelf Too High

The IV.36.6 implementation was intact, but its browser-boundary regression
fixture calculated the plugin root one directory too high.

`TheForgeTellsAStoryRegressionTest` used `dirname(__DIR__, 5)` from
`tests/Unit/Tabletop/Cartography`, which resolves to the WordPress plugins
directory rather than the `great-marketrealm-tabletop` plugin root.

This corrective pass:
- uses the established Cartography regression root of `dirname(__DIR__, 4)`;
- adds explicit `assertIsString()` guards before content assertions so a future
  path mistake reports a clear fixture failure instead of six TypeErrors;
- changes no production IV.36.6 story behaviour.

Expected suite remains 1,188 tests.
