# Phase IV.30.1G.5K — Interior Occupancy & Illustrated Floor Reasoning

## The Mushroom Must Be Standing Somewhere

The Dungeon from Hell demonstrates that a legitimate playable chamber may contain enough illustration — mushrooms, rubble, creatures, statues, furniture or annotation — to make its interior pixels look unlike clean floor. G.5K teaches the Cartographer that locally busy ink inside an otherwise enclosed playable region can represent **occupancy over floor**, rather than a new structural boundary.

The inference is deliberately conservative. A candidate illustrated cell must be enclosed by visible playable surface in at least three cardinal directions, including an opposing pair; it must retain local quiet/playable support; and it must not sit inside a sustained dark horizontal or vertical wall band. Near-white cells remain the responsibility of the ordinary floor classifier, while very dense structural cores are excluded.

Accepted cells are marked as `illustratedInteriorPlayableSurface` and added to the semantic floor mask before G.5J performs short-span occlusion recovery. They do not directly emit pink geometry. G.5J.1's monotonic contract remains authoritative: G.5K may add evidence, but it cannot remove any contour already certified by the G.5A–J baseline.

Hybrid and Living Contour therefore gain a semantic answer to a new question: not merely “does this pixel look like floor?”, but “is this illustrated object plausibly standing inside a floor region?”

**Invariant:** interior illustration may occupy playable surface; it does not become a wall without structural evidence.
