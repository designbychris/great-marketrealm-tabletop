# Phase IV.35.8B.1 — The Atlas Finds the Door

The first Every Dungeon Needs a Door pass added the Way in control to the
Behind-the-Curtain Forge controls, while the Keeper normally creates new
generated Scenes from **The Keeper's Atlas → Generate Scene**.

This corrective exposes the same controlled choice in the Atlas Forge:

- None
- Main entrance
- Arrival portal

The Atlas form now passes the selected mode into the existing shared
`generateSceneForgePlan(...)` pipeline. No second entrance generator or arrival
system is introduced.

> Pippin: “I had drawn the door.”
>
> Keeper: “Where?”
>
> Pippin: “On the other form.”
