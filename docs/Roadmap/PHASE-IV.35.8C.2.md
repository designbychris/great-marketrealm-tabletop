# Phase IV.35.8C.2 — The Checkbox Awakens

The Boss Lair checkbox was rendered disabled and remained disabled even for a
Grand Dungeon because the Behind-the-Curtain availability initialiser executed
before `dungeonForgeLair` had been declared. That JavaScript temporal-dead-zone
error stopped the remainder of Tabletop initialisation, including the later
Keeper's Atlas Boss Lair availability wiring.

The control is now declared before its first availability check. Both Forge
surfaces continue to permit Boss Lairs only for Grand Dungeons.

> Pippin: “The box was asleep.”
>
> Keeper: “Why?”
>
> Pippin: “We asked it a question before introducing ourselves.”
