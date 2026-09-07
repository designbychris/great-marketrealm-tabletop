# Phase IV.35.4C.2 — Light Finds Another Way

## Purpose

Connect the Scene Object `light_occlusion` geometry established in IV.35.4C.1
to the existing authoritative Living Veil illumination projection.

## Rules

- No second lighting engine.
- Carried, dropped, magical and Keeper environmental lights continue through
  `FogOfWarProjector`.
- Each illuminated cell is tested against the persisted Scene Objects for the
  exact Table + Scene.
- Partial occlusion preserves visibility but records authoritative attenuation.
- Complete occlusion removes that light source's contribution to the cell.
- Where multiple lights reach a cell, the strongest surviving transmission wins.
- The browser renders the returned attenuation as a pixel-cell shadow treatment;
  it does not decide illumination.
- Animated emitter sprites remain reserved for IV.35.4C.3.

## Pippin's Field Note

> “The light went around the crate.”
>
> “Technically another lantern reached the square.”
>
> “…please allow me this one.”
