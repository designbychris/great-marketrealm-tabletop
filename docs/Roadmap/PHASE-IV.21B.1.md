# Phase IV.21B.1 — The Steady Lens

The Steady Lens hardens battlefield panning before Fog of War.

Native browser image dragging is disabled, pointer capture owns the complete pan gesture, and camera displacement is calculated from the original pointer-down coordinates rather than accumulating frame-to-frame deltas. A four-pixel threshold prevents ordinary clicks from becoming camera movement.

Tokens and interactive controls remain excluded from the camera gesture, preserving combatant movement and battlefield interaction.
