# Phase IV.37.3 — Pippin Compares Notes

IV.37.3 gives the Keeper the first live comparison between the Forge's prepared
route and the structured facts recorded during the current Session.

## Keeper presentation

Pippin's Adventure Notes now include a **Plan vs. Table** comparison:

- **Prepared Route** shows generated story beats and their Waiting / Current /
  Resolved state.
- **What Actually Happened** shows structured `adventure` Chronicle facts from
  the current Session only.
- A compact footer counts secrets revealed, traps triggered, treasures claimed,
  and story beats resolved.

This is intentionally a factual comparison, not narrative synthesis. The VTT
does not yet claim that a particular trap or treasure fulfilled a particular
prepared beat unless the system has explicit evidence.

## Secrecy hardening

IV.37.2 Adventure facts are Keeper working records. They are now filtered out of
the ordinary Player Chamber Chronicle as well as being absent from the
Keeper-only `adventure_progress` integration projection.

Later recap phases may deliberately publish suitable completed-session facts.
