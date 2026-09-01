# Phase IV.31.1 — The Keeper Strikes a Match

Keeper environmental lights are placed lit by default, then may be doused and relit without deleting the source. The Lantern Rack roster shows each source's current Lit/Doused state and its agreed bright-light radius.

Certified Keeper radii are Torch 20 ft, Lantern 30 ft, Brazier 60 ft, Candle 10 ft, and Magical Light 40 ft. Each retains an equal dim-light band beyond its bright radius through the existing Living Veil model.

The battlefield glow now derives its visual diameter from the authoritative projected range, calibrated grid size, and battlemap reference width instead of relying on source-specific CSS guesses. All five source kinds therefore receive a visible map glow proportional to their illumination while Fog of War remains server-authoritative and wall-aware.

No new lighting engine is introduced. Scene ownership, Behind-the-Curtain preparation, douse/relight persistence, removal, Fog projection, and vision-barrier occlusion continue through the IV.31 Lantern Rack and Living Veil boundaries.
