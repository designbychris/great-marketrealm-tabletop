# Phase IV.22A — The Living Veil

The Living Veil is a presentation refinement over the certified Phase IV.22
Fog of War mechanics.

Unexplored territory now reads as one cohesive field of darkness rather than a
collection of individually shaded cells. Previously explored territory remains
visible as dim, desaturated memory, while currently visible territory remains
fully clear.

Cells bordering current vision receive a stepped pixel-dither treatment, giving
the reveal edge a deliberate 16-bit/SNES character without changing the
underlying grid-based visibility model.

Fog persistence, exploration memory, DM bypass, Player Fog preview and
server-side hidden-token filtering remain unchanged.
