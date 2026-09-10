# Phase V.11B — The Tabletop Opens the Same Book

V.11B lets the Keeper's Bestiary consume canonical monster definitions from Great MarketRealm Expansions, scoped by the Companion Campaign linked to the current Tabletop.

## Authority chain

1. GMREXP publishes and site-activates an Almanac.
2. The Companion DM shares that Almanac with a Campaign.
3. The Tabletop links to that Companion Campaign.
4. The VTT asks the linked Campaign which Almanac keys are currently consumable.
5. The VTT reads only `monster` definitions from those Almanacs through GMREXP's Active Content API.

No expansion monster is copied into Tabletop persistence. A summoned token is still a Scene-owned combat snapshot, exactly like every existing Bestiary deployment.

## Safety boundaries

- Unlinked Tables do not receive expansion monsters.
- Almanacs not shared with the linked Campaign do not appear.
- Almanacs deactivated in GMREXP stop being consumable even if the Campaign remembers its selection.
- Deployment re-resolves the campaign-scoped Bestiary at action time, preventing stale UI from summoning a monster after access has changed.
- Free-form monster action prose is not guessed into attack dice, range, or damage mechanics. Only explicit structured attack rules may enter the live combat arsenal.
- Companion-native Bestiary shelves remain available and are not replaced.

## Presentation

Expansion Bestiary cards carry sourcebook provenance. Midnight Menu receives the first Almanac-specific visual treatment, including an explicit text badge so source identity is not communicated by colour alone.
