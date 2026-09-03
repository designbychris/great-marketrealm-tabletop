# Phase IV.34.6 — Until Next Time

Phase IV.34.6 gives the Keeper a safe, explicit closing moment immediately after ending a Session without confusing Session closure with campaign closure.

## Behaviour

- Ending a Session still preserves the active Table/campaign exactly as before.
- The newly completed Session projection carries its authoritative started/ended timestamps and calculated duration.
- Immediately after the Keeper ends a Session, the refreshed Chamber reveals a one-time **Until Next Time…** farewell.
- The farewell confirms the Session title, conclusion time, duration, recorded deeds, and continuing campaign state.
- **View Recap** expands and focuses the existing **Previously, in the MarketRealm…** recap rather than creating a second history surface.
- **Close Farewell** dismisses the transient message.
- A browser refresh does not resurrect an old farewell: the one-time marker is consumed from session storage when shown.
- The persistent Session recap remains available normally between Sessions.

## Boundaries

This phase does not end/archive a campaign, publish Keeper-only recap prose, create a new Chronicle record, reset Scene state, or start the next Session. Resume behaviour belongs to IV.34.7 — The Next Gathering.
