# Phase IV.19.1 — Chronicle & Fallen Combatant Presentation

## Chronicle repair

The IV.19 browser pass exposed a CSS inheritance collision: generic
`.gmrt-party li` rules intended for the member list were also styling the
nested Battle Chronicle entries.

IV.19.1 scopes member styles to the direct party list and gives Chronicle rows
their own layout. Entries now grow with wrapped content rather than overlapping
inside fixed-looking rows. The Chronicle keeps a bounded scroll viewport with
stable scrollbar space.

## Combatant presentation states

The server now projects a semantic presentation state for every visible token:

- Healthy
- Wounded
- Downed
- Defeated
- Deceased

These are presentation states, not a second combat rules engine.

### Downed

A character token at 0 HP whose death is not confirmed is Downed. Stable
characters at 0 HP are still Downed for board presentation.

### Defeated

A non-character token at 0 HP is presented as Defeated unless death has been
explicitly confirmed. The first visual badge is `KO`.

This gives ordinary monsters/NPC objects a clear out-of-fight state without
asserting that every 0 HP creature is canonically dead.

### Deceased

A token whose Death Save State reports death is Deceased. This is intentionally
different from simply reaching 0 HP.

## Future pixel-art integration

Token markup now exposes `data-combatant-state` and stable CSS hooks:

- `is-state-healthy`
- `is-state-wounded`
- `is-state-downed`
- `is-state-defeated`
- `is-state-deceased`

The temporary letter-token presentation uses DOWN / KO / DEAD badges and simple
opacity/pose treatment. Future race sprites can replace those visuals without
changing combat semantics.

The Tabletop refresh endpoint exposes `combatant_states`, keeping the browser a
consumer of the server projection rather than teaching JavaScript how death
works.
