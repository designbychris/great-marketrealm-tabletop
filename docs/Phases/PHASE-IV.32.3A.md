# Phase IV.32.3A — The Deeds Remember Every Adventurer

Player-originated Satchel rolls must appear in **Deeds at the Table** immediately when an Encounter is active, while Exploration rolls continue to belong to **Tales from the Chamber**.

The client now supplies the Encounter already rendered in the live Chamber as a routing hint for Quick Hands, Weapons to Hand and Spell Pouch rolls. The server never trusts that hint blindly: it resolves the Encounter from the authoritative repository, rejects ended Encounters, and requires the hinted Encounter to belong to the current active Scene before using it. Normal active-Scene Encounter discovery remains the first choice.

This closes the timing edge where a Player roll could be filed in the Chamber Chronicle even though that Player was already looking at the Battle Chronicle. DM-originated behaviour and the existing Battle/Chamber ledger split remain unchanged.

The Adventurer's Satchel also adopts the same restrained pixel scrollbar language already used by the Keeper drawers.
