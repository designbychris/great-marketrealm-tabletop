# Phase IV.35.8D — Pippin Draws the Way Home

The Keeper may now draw a persistent route from the Scene currently before the
Atlas to another Scene on the same Table.

Routes are deliberately **Keeper-controlled** in this first pass. A route
records the destination Scene and declares that Scene's existing **Party
Arrival Threshold** as the authoritative arrival anchor. This means forged Main
Entrances and Arrival Portals immediately participate without a second spawn or
teleport system.

The Keeper can draw, erase, or take the Table through a route. Travelling uses
the existing Keeper's Atlas activation boundary; players then enter the newly
active Scene through the existing arrival-threshold behaviour.

The current Scene is never offered as its own destination, and both route ends
must belong to the same Table.

> Pippin: “I have drawn where the door goes.”
>
> Keeper: “And the other end?”
>
> Pippin: “Also on the map. I checked this time.”
