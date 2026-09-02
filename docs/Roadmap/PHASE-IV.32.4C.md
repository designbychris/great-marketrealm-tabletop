# Phase IV.32.4C — The Keeper Lights the Pixels

**Status:** implemented; PHPUnit and browser certification pending.

This phase gives the Keeper's existing battlefield light sources a restrained 16-bit identity without changing illumination authority, calibrated radii, Fog projection, line-of-sight, placement, lit/doused persistence, or Scene ownership.

## Presentation contract

- **Torch** uses a small irregular stepped flicker and a CSS-drawn pixel torch marker.
- **Candle** uses a quicker, gentler flutter and a tiny pixel candle marker.
- **Lantern** remains comparatively steady, with a slow restrained breathing glow and a framed pixel lantern marker.
- **Brazier** carries a broader, slower pulse and a heavier pixel brazier silhouette.
- **Magical Light** uses a cool stepped shimmer rather than flame-like motion, with a CSS-drawn pixel star marker.
- Doused environmental lights retain a dormant Keeper-visible marker but lose the animated glow.
- `prefers-reduced-motion` disables all new environmental-light animation while preserving the static illumination treatment.

## Authority boundary

The existing `EnvironmentalLightAjaxController` presets remain unchanged at Torch 20+20 ft, Lantern 30+30 ft, Brazier 60+60 ft, Candle 10+10 ft and Magical Light 40+40 ft. The browser continues to render only server-projected `light_sources`; no client light radius, Fog cell, visibility, LOS, placement, or persistence rule is introduced by this phase.
