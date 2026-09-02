# Phase IV.33.4 — The First Map on the Table

A Keeper-created campaign now asks how its opening Scene should reach the Table instead of silently choosing one path for every campaign.

## First-map paths

- **Begin with a Blank Table** preserves the certified `The First Blank Page` generated Scene.
- **Choose from the Atlas** copies one of the Keeper's uploaded image-backed Atlas Scenes into the new campaign, preserving the map attachment, dimensions and calibrated grid while leaving the source Scene untouched.
- **Ask Pippin to Forge a Scene** creates a clean `Pippin's Forge Workbench` Scene, enters the new campaign and opens the existing authoritative Scene Forge ready for Dungeon, Forest or Village generation.

## Authority boundary

Atlas sources are resolved server-side and must belong to a Table owned by the current Dungeon Master. Generated/forged Scenes are not presented as reusable Atlas surfaces because their authoritative world state lives beyond the bare map surface; those campaigns should begin through the Forge path instead.

The existing WordPress authentication, Tabletop AJAX nonce, Dungeon Master creation policy, Table ownership, Scene persistence and Forge authority remain unchanged.

## Regression contract

Phase IV.33.4 adds coverage for the three creation paths, Keeper ownership of reusable Atlas maps, independent Scene cloning with grid calibration, and the Forge-first browser hand-off.
