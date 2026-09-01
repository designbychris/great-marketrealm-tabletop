# Phase IV.32.2 — The Atlas Gets a 16-Bit Makeover

## Purpose

Extend the presentation-only Pixel Chisel established in IV.32.1 into the Keeper's working drawer without changing any Tabletop authority or behaviour.

## Scope

This pass gives the Keeper's Atlas, mapped Scene cards, Scene Forge, Threshold Markers, Cartography Assistant, Dungeon/Scene Forge controls and Lantern Rack one coherent SNES-era interface vocabulary.

The treatment uses the existing pixel tokens from IV.32.1: stepped two-pixel borders, hard shadows, dark timber/ink surfaces, brass highlights, square controls, compact monospace labels and explicit focus/active/disabled states.

## Scene register

Scene cards now read as small cartridge-era map records rather than generic web cards. The active Scene receives a green registration edge, generated Scenes receive a compact `FORGED` badge, thumbnails use hard square framing, and Scene actions remain clearly grouped below the record.

## Keeper workbench

Atlas Forge, Threshold tools, Cartography Assistant, Lantern Rack and the Scene Forge share the same inset workbench treatment. Native details/summary controls retain their semantics while gaining explicit pixel disclosure arrows.

## Lantern Rack

The Rack retains its existing placement and Lit/Doused behaviour. Its visual states are strengthened so the selected light type and source state remain legible inside the darker pixel shell.

## Accessibility and authority

No JavaScript or persistence behaviour is changed by this phase. Existing keyboard focus supplied by IV.32.1 remains in force, native details semantics are preserved, and reduced-motion behaviour remains unchanged. This phase is a skin over the certified Tabletop rules engine.

## Browser certification

Browser certification should verify the closed/open Atlas drawer, active and generated Scene cards, Forge controls, Cartography details, Threshold tools and Lantern Rack at desktop and narrow viewport widths. Functional Scene switching, Forge generation, light placement and cartography actions should behave exactly as before.
