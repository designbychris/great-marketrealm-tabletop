# Phase IV.35.8C — Something Large Lives Here

Grand Dungeon Forge maps may optionally reserve a **Boss Lair**.

The lair is created before ordinary room packing, guaranteeing a substantially
larger chamber without changing Compact or Standard generation. It carries an
explicit `lair` room role through the server boundary so later encounter logic
does not need to rediscover the largest room.

Furniture in the lair is deliberately sparse and perimeter-biased, leaving a
large central combat floor and normal doorway clearance. Custom Furniture may
also opt into the controlled **Boss lair** Forge label.

The option is available on both Forge surfaces but enabled only when
**Environment = Dungeon** and **Scale = Grand**.

> Pippin: “I have left the middle empty.”
>
> Keeper: “For the boss?”
>
> Pippin: “For running away from the boss.”
