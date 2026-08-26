# Phase IV.7 — The Tabletop Chamber

## Purpose

Open the first visible Great Marketrealm Tabletop front-end and prove that
the domain foundations built in IV.1 through IV.6 can assemble into one
coherent Table view.

## Route ownership

Great Marketrealm Tabletop owns:

`/tabletop/`

and supports a Table-specific form:

`/tabletop/{table-id}/`

No manually created WordPress Page is required.

## Access

The chamber requires a logged-in WordPress user.

A Table-specific chamber additionally requires an Active Table membership.

Invited, Left and unrelated users cannot enter another Table's chamber.

## Chamber state

The server assembles:

- Table record
- current viewer membership
- Table gathering
- active Scene
- active Scene tokens

The chamber remains server-authoritative.

## Dungeon Master and Player views

Dungeon Masters receive all tokens on the active Scene, including Hidden
tokens used during encounter preparation.

Players receive Visible tokens only.

This is not fog of war. It is the first DM-only visibility boundary.

## Battlemap rendering

The active Scene's WordPress Media attachment is rendered as the battlemap.

Square-grid Scenes receive a visual grid overlay.

Gridless Scenes render without the overlay.

## Token rendering

IV.6 token records are rendered using their normalised X/Y coordinates and
footprint values.

The first visual token uses its label initial rather than resolving Companion
or Bestiary artwork. External artwork integration remains a later concern.

## The Gathering

The chamber includes a persistent member sidebar so the Table is visibly a
shared space rather than merely a map image.

## Read-only first chamber

IV.7 deliberately does not implement token dragging.

The initial shell proves routing, access control, state assembly, battlemap
rendering, token placement and role-sensitive visibility before write
endpoints are exposed to browsers.

## Next

Phase IV.8 — The Living Table introduces interactive movement and incremental
state refresh while keeping the server authoritative.
