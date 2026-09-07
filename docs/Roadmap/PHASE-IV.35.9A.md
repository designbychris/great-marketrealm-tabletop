# Phase IV.35.9A — The Furniture Stops Pretending

Browser certification of IV.35.9 clarified the intended conversion semantics:
**Convert to Mimic is an immediate transformation**, not merely a hidden flag.

The chooser now contains only Bestiary records identifiable as Mimics by
canonical name, creature kind, or trait. The server repeats that validation so
a crafted request cannot convert furniture into an arbitrary Bestiary monster.

On conversion the existing Bestiary deployment boundary creates one visible
creature token at the furnishing's exact coordinates and provisions its combat
profile. Only after that succeeds is the Scene Object removed. The chamber is
then refreshed so the furniture visibly becomes the chosen Mimic.

The dialog has also received the MarketRealm dark/pixel treatment while keeping
Pippin's warning intact.
