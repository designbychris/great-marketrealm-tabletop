# Phase IV.36.4A — Pippin Can Reach the Buttons

Browser correction for the first trap pass. Revealing a trap now produces an obvious concealed-to-revealed visual transition and propagates to an already-open Player Tabletop through the existing five-second refresh cycle. Player AJAX state now applies the same secrecy boundary as the initial chamber render, so unrevealed traps/secrets do not leak through live state. Trap controls are attached directly to the marker hover target with a small pointer bridge, removing the dead gap that made the buttons difficult to reach.

Expected suite: 1,152 tests.
