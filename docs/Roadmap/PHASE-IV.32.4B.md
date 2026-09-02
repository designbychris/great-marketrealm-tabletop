# Phase IV.32.4B — The Veil Goes 16-Bit

**Status:** implemented; PHPUnit and browser certification pending.

This phase gives the existing Living Veil a 16-bit battlefield presentation without changing Fog authority, explored-cell persistence, line-of-sight geometry, vision radii, doors, lighting, or token visibility.

## Presentation contract

- **Unknown territory** remains fully obscured, now with restrained near-black pixel dithering.
- **Remembered territory** remains the existing explored-but-not-currently-visible state, now presented as a faded, desaturated map memory rather than ordinary dark Fog.
- **Current sight frontier** uses a stepped brass/parchment rim on the already-projected edge cells.
- **Memory frontier** uses a colder dark hatch between explored memory and absolute unknown territory.
- **Keeper Player Fog Preview** gains a private pixel frame and label only while the existing DM bypass and Preview Player Fog state are active.
- Reduced-motion preferences continue to disable Fog transitions.

## Authority boundary

No server Fog projector, Fog repository, LOS mapper, Vision barrier, door, light-source, token-visibility, exploration-persistence, or movement authority is changed. The browser continues to consume the same `visible` and `explored` sets and the same existing Fog cell classes.
