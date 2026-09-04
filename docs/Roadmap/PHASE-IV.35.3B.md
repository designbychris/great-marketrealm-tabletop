# Phase IV.35.3B — Pippin Rearranges the Furniture

The Scene Object foundation now becomes a practical Keeper editing surface. IV.35.3A proved that furniture can actually reach the floor; IV.35.3B lets Pippin regret where the Keeper put it.

## Keeper workflow

- The Furniture Palette now lives inside **Dungeon Master Controls** rather than taking permanent Chamber space.
- Choose a catalogue furnishing to enter placement mode exactly as certified in IV.35.3A.
- Select an existing furnishing directly on the Scene.
- Drag the selected object to move it using the shared battlemap-aware coordinate helper.
- Rotate left/right in 15-degree steps.
- Scale between 0.5× and 2.5× in 0.25× steps.
- Duplicate a furnishing with a small positional offset.
- Delete a selected furnishing from that Scene.

## Authority and persistence

Every mutation is processed through the authenticated Chamber boundary. The server requires Dungeon Master authority, verifies the Scene Object authoring nonce, verifies the submitted projected Scene with `hash_equals`, and re-resolves the object by Table + Scene + object identity before mutation. Existing `SceneObject` and `WordPressSceneObjectRepository` persistence remains authoritative.

Behind-the-Curtain preparation therefore edits only the selected preparation Scene; live Players remain bound to the live Scene. Players render the object layer but receive no editing controls or pointer-active furniture.

## Battlefield interaction rule

The Scene Object layer itself remains `pointer-events: none`. Only individual furnishings become pointer-active in Keeper view. This prevents the object layer from becoming an invisible battlefield blanket and preserves ordinary token, Lens, Fog, vision and cartography interaction in empty map space.

## Deferred

Collision, cover and vision obstruction remain IV.35.4. Open/closed and other interactive states remain IV.35.5. The universal Mimic conversion seam remains reserved for the later Bestiary-backed workflow.

**Pippin's note:** “Moving the table after I measured it is, technically, a new map.”
