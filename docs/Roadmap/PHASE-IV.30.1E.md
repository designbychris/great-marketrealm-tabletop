# Phase IV.30.1E — The Cartographer's Lens Controls

## Purpose

Bring the existing Cartographer's Lens controls onto the battlemap itself so zoom, fit and reset remain available at the point of work, in the familiar manner of map applications.

## Behaviour

- The existing Lens transform state remains the single source of truth; no second zoom implementation is introduced.
- A compact overlay inside the Lens stage exposes Zoom In, Zoom Out, current zoom percentage, Fit and Reset.
- Fit leaves a small visual gutter around the map instead of pressing artwork flush against the viewport edge.
- Reset returns to the established 100% scale and origin.
- Zoom buttons disable at the existing 25% / 300% limits.
- Panning remains available by dragging the battlefield; buttons are excluded from drag-start handling by the existing interactive-target guard.
- The controls are client-side presentation only and create no server mutation route.

## Certification

- PHPUnit regression coverage protects placement, accessibility, reuse of the existing Lens functions, bounded zoom state and documentation/version identity.
- Browser certification should verify the controls remain fixed over the map while the viewport pans/zooms beneath them.
