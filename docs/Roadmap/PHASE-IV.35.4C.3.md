# Phase IV.35.4C.3 — The Flames Begin to Dance

## Purpose

Give the Keeper's existing pixel light markers a living SNES-style presentation
without changing any authoritative lighting rules.

## Presentation

- Torch — stepped flame dance with small rising embers.
- Candle — tiny flame flutter and occasional spark.
- Lantern — slow pixel sway and contained heartbeat.
- Brazier — larger stepped fire dance with several embers.
- Magical Light — hovering shimmer with pixel sparks.
- Existing dropped-torch animation remains intact.

The surrounding environmental glow may breathe by only a few percent. This is
presentation-only and never changes bright/dim feet, Fog, LOS, Scene Object
occlusion or server visibility.

Doused lights are static. `prefers-reduced-motion` disables emitter and glow
animation while preserving the same visual identity.

## Pippin's Field Note

> “The brazier is moving.”
>
> “The fire is animated.”
>
> “The light is breathing.”
>
> “Only visually.”
>
> “…that sentence has made things worse.”
