# Phase IV.30.2B — Beyond the Dungeon Walls

Pippin leaves the dungeon. The procedural Forge is promoted from a dungeon-specific generator into a deterministic **Scene Forge**.

## Architectural contract

- **Environment / Scene Type chooses topology.** The initial generators are Dungeon, Forest and Village.
- **Theme chooses presentation.** Changing theme for the same environment, seed and scale must not secretly select dungeon topology or alter the environment contract.
- All generated plans remain Tabletop-native and flow through the existing square grid, Vision barriers, Fog, Keeper lights, tokens and encounter machinery.
- Existing Dungeon generation remains the compatibility/default path.

## Forest topology

Forests use the whole Scene as traversable ground rather than room-and-corridor floor. Deterministic clearings are connected by a winding trail, while dense tree clusters, rock clusters and fallen logs create tactical structure and authoritative LOS barriers. Suggested camp lights are placed in major clearings.

## Village topology

Villages use open traversable ground with a through-road, village square, 5–10 buildings by scale, an inn, well, fenced garden, trees and tactical lanes. Buildings are authoritative LOS obstacles and expose real Forge door barriers where their entrances meet the road. The same Scene can begin as roleplay and immediately enter Start Encounter without changing maps.

## Composition

The plan persists `scene_type` independently from `theme`. A Village can therefore be presented with Frostreem, Rootland or Mushroom-flavoured treatment without becoming a dungeon; likewise a Forest retains forest topology regardless of its Great Marketrealm theme.

## Safety

All generated barriers remain within the existing 200-object review budget. Forge-authored barrier coordinates stay normalised in the browser and are converted at the server boundary to Vision's rules-grid coordinates, preserving the certified IV.30.2A.1A corner-tile corrective.
