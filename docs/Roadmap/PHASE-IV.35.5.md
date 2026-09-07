# Phase IV.35.5 — Please Do Not Open the Chest

Scene Objects gain persistent interaction state.

## First interaction vocabulary

- `none`
- `open_close`

The Chest is the first `open_close` furnishing. Newly placed chests start
closed. The Keeper can select one and Open/Close it from the existing furniture
editor. The state persists on the Scene Object and survives the normal Chamber
refresh path.

Open/closed state is presentation and interaction state; it does not silently
rewrite movement, cover, vision or light traits.

This phase deliberately does **not** add loot transfer, inventories, traps,
locks, or Bestiary-backed Mimic conversion. Those can build on the same state
boundary later.

> Pippin's advice: “Don't.”
