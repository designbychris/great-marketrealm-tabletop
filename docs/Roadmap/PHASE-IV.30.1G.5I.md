# Phase IV.30.1G.5I — Playable Region Closure & Perimeter Inference

## Pippin Maps the Floor Outward

The Dungeon from Hell demonstrated that a genuine chamber perimeter can disappear before it ever becomes a trustworthy contour chain. In heavily illustrated regions, creature lines, stones, hatching and other ink can interrupt the otherwise obvious playable floor. G.5I therefore adds a second direction of reasoning: **certified playable space may provide evidence for where its perimeter must be**.

This is deliberately not a generic white-area outline. Living Contour starts only from floor already accepted as playable after exterior-whitespace classification. It performs two small local closure passes to recover samples surrounded by that same playable context, refuses to absorb known exterior whitespace, and limits recovery through very dense ink. A recovered playable-to-non-playable transition still needs nearby wall-body ink before it can become an inferred perimeter edge.

Certified structural thresholds remain vetoes. Region closure cannot seal a recognised doorway merely to make a room look complete, and inferred perimeter edges receive their own evidence model so later phases and Keeper review can distinguish them from directly observed contour chains.

**Cartographer's rule:** floor can testify about its boundary, but it cannot invent one without corroboration.
