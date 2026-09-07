# Phase IV.35.6 — Furnishings of the MarketRealm

The Keeper's Furniture Palette grows from six foundational pieces to sixteen.

## New catalogue pieces

- Bed
- Desk
- Bench
- Cupboard
- Sacks
- Weapon Rack
- Market Stall
- Campfire
- Stool
- Rug

Each piece is an ordinary Scene Object and therefore inherits the certified
placement, manipulation, collision, cover, vision, light-occlusion and future
Mimic architecture. Traits differ intentionally: a Rug is harmless decoration,
a Cupboard is a tall sight blocker, and a Market Stall creates substantial
tactical cover.

## Forge integration

IV.35.7's deterministic furnishing planner immediately gains the larger
vocabulary. Quarters can contain beds/desks, studies use desks, stores gain
cupboards/sacks, mess rooms gain benches/stools, and forest camps can finally
contain campfires.

No new authoring route is introduced. The existing catalogue-driven Keeper
Palette discovers all ten pieces automatically.

> Pippin: “I asked for a larger office.”
>
> Keeper: “We bought you sixteen kinds of furniture.”
>
> Pippin: “That is not the same thing.”
