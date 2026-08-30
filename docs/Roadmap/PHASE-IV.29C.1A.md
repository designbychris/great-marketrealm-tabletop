# Phase IV.29C.1A — Roll for Damage

A successful attack should feel like two deliberate acts at the table: first the d20 earns the hit, then the acting human rolls the attack's damage. Guild Diceworks now owns that second visible moment without surrendering server authority.

## Contract

- A hit creates a server-owned, single-use pending-damage receipt identified by the persisted `attack-resolved` Battle Event.
- The browser receives only the opaque attack-event ID and a presentation copy of the authoritative formula. It never supplies dice, modifier, damage type, target, critical state, defenses or HP.
- The acting Player may consume damage for their controlled adventurer; the Dungeon Master may consume damage for the active combatant.
- The receipt is valid only while that attacker still owns the current turn.
- Misses create no pending damage.
- Critical hits double damage dice through the existing DamageResolver.
- Resistance, vulnerability and immunity continue through DamageDefenseResolver before Vitality changes.
- Damage at zero and massive-damage consequences continue through the existing death-save state.
- A persisted `damage-applied` event links back to `attack_event_id`, making the receipt single-use and keeping Chronicle attack/damage pairing intact.

## Presentation

Guild Diceworks reveals **Roll Damage** only after a certified hit. The control shows the authoritative formula, marks critical damage when appropriate, displays the certified damage dice/result, and then yields the resolved damage, defense effect and remaining HP.

## Boundary

This phase applies to attacks already represented by the Tabletop Combat Arsenal: Companion weapons and Bestiary attacks today, with future spell attacks able to reuse the same receipt boundary when they join authoritative battlefield targeting.

Next: **IV.29D — The Keeper's Menagerie**.
