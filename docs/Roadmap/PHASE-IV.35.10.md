# Phase IV.35.10 — Something Is Waiting in the Lair

A Grand Dungeon Boss Lair may optionally be assigned one creature from the
existing Keeper's Bestiary during Forge creation. The selector appears only
when the semantic Boss Lair is enabled.

The server accepts an occupant only when the normalised plan contains a genuine
Boss Lair, validates the creature against the canonical Bestiary, computes the
centre of that room, and deploys it through the existing Bestiary deployment
and combat-provisioning boundary.

The occupant is hidden from Players by default, allowing the Keeper to prepare
the lair without revealing what waits there. The Forge projection records both
the Bestiary definition and deployed token for later Boss Lair semantics.

> Pippin: “I knew the large empty room was suspicious.”
