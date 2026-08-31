# Phase IV.30.1D — The Cartographer's Judgement
## Hybrid Structural & Living Contour Analysis

Phase IV.30.1D teaches the Keeper's Cartography Assistant to treat a single battlemap as a mixture of cartographic languages rather than forcing one global tracing strategy upon it.

The Assistant still offers Structural tracing and Living Contour independently. The new **Judgement · hybrid map** mode runs both readers and makes conservative local choices between them. Constructed rooms and corridors are favoured where repeated nearby parallel structural segments provide evidence of deliberate architecture. Irregular cave and rock boundaries retain the ordered polyline geometry produced by Living Contour.

Where both readers describe the same local horizontal or vertical wall, the hybrid pass may suppress the overlapping Living Contour span in favour of strong structural linework. It never reconnects across a removed span, so a doorway or uncertain opening is not silently bridged merely to produce prettier geometry. Ambiguous regions remain conservative and reviewable.

The hybrid review budget remains bounded to 200 objects. Polyline contours are kept first because one path can represent many LOS spans efficiently; the remaining object budget is spent on the strongest locally-supported structural suggestions. Existing server-side polyline and batch-vertex limits remain authoritative when the Keeper applies the reviewed draft.

Nothing is saved automatically. The Keeper still reviews, selects and explicitly applies suggestions. Existing Scene ownership, Behind-the-Curtain preparation and fog refresh behaviour remain unchanged.

### Benchmark contract

The **Control — regular dungeon** remains the primary Structural tracing benchmark. The **Advanced — cave dungeon** remains the primary Living Contour benchmark. The mixed hand-drawn dungeon with rectangular rooms, corridors, hatch-heavy rock, irregular cave transitions, stairs and handwritten annotations becomes the primary **Hybrid Judgement benchmark**. Success means useful structural linework through deliberately built areas, continuous organic polylines through cave areas, minimal tracing of printed grid/hatch/annotation noise, and no invented bridges across genuine openings.

The hostile misaligned-grid benchmark remains reserved for later grid-registration work. IV.30.1D must not compensate for an artwork/grid registration error by distorting its local classification rules.
