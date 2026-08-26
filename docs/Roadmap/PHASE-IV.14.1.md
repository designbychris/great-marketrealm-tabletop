# Phase IV.14.1 — The Steward's Test Table

The empty `[great_marketrealm_tabletop]` host now offers **Prepare Test Table**
to signed-in users.

It creates a real Table named **Sage's Combat Testing Grounds**, a real active
Scene named **The Training Yard**, and imports a bundled neutral battlemap into
WordPress Media.

The fixture includes Auby, Training Slime, Frosty Cheese Thing, and Suspicious
Training Dummy. Each receives persisted HP, Armor Class, attack modifier, and
damage data. Auby is controlled by the user who prepared the Table.

A real Encounter is prepared, populated, and started using the existing domain
services. This is deliberately not a second demo combat engine.

Provisioning is idempotent: an existing open test Table owned by the same user
is reused. Phase IV.15 can now add resistance, vulnerability, and immunity to
these fixture creatures for immediate browser testing.
