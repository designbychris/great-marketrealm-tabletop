# Phase IV.32.4A — The Battlefield Finds Its Pixels

IV.32.4A carries the Tabletop's established SNES-era presentation language onto the battlefield itself. This is a **presentation-only** pass: it does not alter rules-grid geometry, token coordinates, movement validation, targeting authority, Vision, Fog, encounters or persistence.

## Battlefield vocabulary

The calibrated square grid keeps its existing size, offset and visibility values while exchanging generic white browser ink for the Tabletop parchment/pixel vocabulary. Tokens keep the same dimensions and hit areas, but gain harder shadows and pixel rendering. Selected miniatures use a four-corner focus reticle; the existing authoritative active-turn state keeps its stronger stepped turn cursor.

Target measurement continues to use the existing server-authoritative targeting layer. Its SVG line, long-range cadence, out-of-range cadence and range plaque receive square, crisp-edged presentation only. Keeper deployment thresholds likewise keep their exact coordinates and behaviour while moving to square 16-bit markers.

Vision doors keep their existing barrier identities and LOS behaviour. Closed/open doors merely receive clearer square-dash editing marks. Existing Footsteps Through the Veil remain the movement trail and keep their existing projection/visibility rules while receiving a harder pixel print.

## Guard rails

- No JavaScript movement or targeting algorithm changes.
- No token coordinate, scale, drag or persistence changes.
- No Vision barrier geometry or door authority changes.
- No Fog reveal/exploration changes.
- No encounter or initiative authority changes.
- Existing accessibility focus remains visible through the selected-token reticle.
- Reduced-motion users receive no added battlefield animation.

## Browser-certification polish

The combat deed strip now uses an adaptive minimum-width grid instead of forcing all five actions into five equal columns. Wide docks retain the single five-button row; narrower Satchel/Bestiary combat docks naturally wrap into a balanced multi-row layout (typically 3 + 2) before labels can overflow. Deed labels remain on one line, so `Disengage` stays intact without shrinking the established pixel typography. This is CSS-only and does not change deed actions or combat authority.

## Final browser-certification polish

The final IV.32.4A polish keeps long combat deed labels inside the dock, lets the Guild Diceworks certified attack result reflow against the **actual combat-dock width** rather than the viewport, and treats attack/damage results as transient turn UI. When the authoritative Encounter changes combatant or advances the round, the old interactive roll card is cleared while the permanent Deeds at the Table Chronicle remains untouched.
