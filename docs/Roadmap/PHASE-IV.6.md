# Phase IV.6 — Tokens on the Table

## Purpose

Place persistent game pieces onto the Scene foundation created by IV.5
without prematurely introducing browser dragging or real-time networking.

## Token identity

Every token records:

- stable GMRT token ID
- owning Table ID
- owning Scene ID
- display label
- token type
- optional opaque source reference
- optional controlling WordPress user ID
- normalised X/Y coordinates
- width/height footprint
- visibility
- creation time

## Token types

The first vocabulary is deliberately small:

- Character
- Creature
- Object

Character and Creature tokens require an opaque source reference.

Examples may eventually be Companion Character IDs or Bestiary creature
IDs, but the Tokens domain does not know how those external records are
stored.

Object tokens may stand alone with no external source.

## Controller identity

A token may optionally store the WordPress user ID that controls it.

IV.6 records that relationship only. Permission enforcement for actual
player movement belongs with the visible Tabletop interaction layer.

## Placement and movement

Tokens use the normalised coordinate system introduced by IV.5.

Both X and Y remain between `0` and `1`, which keeps persisted placement
independent of viewport size and rendered battlemap pixels.

## Footprint

Tokens store independent width and height values in Scene/grid units.

This supports foundations for:

- ordinary 1×1 adventurers
- larger monsters
- wide objects
- future non-square props

## Visibility

The initial visibility states are:

- Visible
- Hidden

This is a Dungeon Master preparation primitive, not fog of war.

## Scene persistence

Tokens belong to a Scene. Switching the Table's active Scene does not
delete tokens from any other Scene.

## Ended Tables

Tokens remain readable after a Table ends, preserving session history.

Placement, movement, resizing and visibility changes are rejected after
the Table has ended.

## The visible Tabletop

The front-end VTT shell remains reserved for:

**Phase IV.7 — The Tabletop Chamber**

Its intended route is:

`/tabletop/`

IV.7 will render the battlemap and token records established here.

## Non-goals

IV.6 does not implement:

- the `/tabletop/` UI itself
- dragging
- browser movement events
- sockets/polling
- token artwork resolution
- Character ownership verification
- Bestiary fetching
- initiative
- conditions
- fog of war
- collision
- measurement
