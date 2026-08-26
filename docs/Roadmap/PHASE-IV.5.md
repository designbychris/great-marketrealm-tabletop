# Phase IV.5 — The First Battlemap

## Purpose

Give every Table its first persistent visual play surface without prematurely
building token movement, fog of war or real-time networking.

## Scenes

A Table may retain multiple Scenes. Each Scene records a stable ID, Table ID,
name, WordPress Media attachment reference, pixel dimensions, grid
configuration and creation time.

Exactly one Scene may be active at a Table. Switching the active Scene does
not delete or overwrite the previous Scene.

## Grid foundation

The first supported modes are deliberately small:

- Square grid
- Gridless

Square grids require a positive grid size. Gridless Scenes store a grid size
of zero.

Hex grids are intentionally deferred until their coordinate and token
footprint rules can be designed properly.

## Token-ready coordinates

IV.5 does not create tokens. It establishes normalised X/Y coordinates from
0 through 1 so later token placement remains independent of the battlemap's
rendered pixel size.

## Media ownership

GMRT stores the WordPress Media attachment ID for the battlemap. It does not
duplicate image bytes into Table records.

## Ended Tables

Scenes remain readable after a Table ends, preserving session history.
Creating or switching Scenes is rejected once the Table has ended.

## Non-goals

This phase does not yet implement token records, dragging, initiative, fog of
war, drawing tools, measurement, live sockets, player pings, or viewport UI.
Those systems now have a stable Scene surface to build upon.
