# Phase IV.35.8 — The Keeper Opens the Furniture Catalogue

The sixteen certified built-in furnishings remain code-owned while WordPress
administrators gain a persistent custom-furniture registry under
**Tools → Furniture Catalogue**.

Custom definitions carry the same dimensions and tactical traits used by every
Scene Object: movement blocking, cover, vision blocking, light occlusion,
interaction, Mimic capability, and an opt-in Dungeon Forge flag.

## SVG boundary

Custom sprites are uploaded as SVG files (maximum 256 KB) and sanitised before
persistence. The accepted vocabulary excludes scripts, event handlers,
`foreignObject`, embedded CSS, external/data URLs and other active content.
The SVG is decorative only; tactical authority remains in the definition.

## Runtime

`FurnitureCatalogue` merges immutable built-ins with custom definitions. Built-
in keys win collisions, so administrator content cannot silently replace core
rules. The existing Keeper Palette discovers custom definitions automatically;
placed custom pieces persist their safe sprite with their normal Scene Object
properties.

IV.35.8A will teach the Forge how to select opt-in custom furniture using
explicit room/usage labels instead of hardcoded kind names.

> Pippin: “Who added the ottoman?”
>
> Keeper: “Administration.”
>
> Pippin: “It has a field called mimic-capable.”
>
> Keeper: “Future-proofing.”
>
> Pippin: “I object to the future.”
