# Phase IV.8 — The Living Table

## Purpose

Make the Tabletop Chamber interactive while preserving the server as the
authority over every shared gameplay mutation.

## Movement authority

The browser never writes token state directly.

It sends a movement request containing:

- Table ID
- token ID
- target normalised X/Y coordinates
- expected token revision
- authenticated Tabletop nonce

PHP validates the current Table, membership, active Scene, token ownership,
movement permission and revision before persisting the new position.

## Dungeon Master movement

An Active Dungeon Master may move any token on the active Scene.

## Player movement

An Active Player may move a Character token only when all of these match:

- the token is a Character token
- the token controller user ID equals the Player user ID
- the Player has selected a Companion Character for this Table membership
- the token opaque source reference equals that selected Character ID

Players cannot move creatures, objects or another Player's Character.

## Stale state protection

Tokens now carry a monotonically increasing revision number.

A movement request includes the revision the browser believes it is moving.
If the persisted token revision has already changed, the request is rejected
as an HTTP 409 conflict rather than overwriting newer Table state.

## State refresh

The Chamber requests authoritative Table state every five seconds.

This is intentionally lightweight polling rather than WebSockets. It proves
the shared-state contract first.

A failed optimistic movement refreshes authoritative state.

## Interaction

Tokens are keyboard-focusable.

Arrow keys move the selected token in small normalised increments. Holding
Shift uses a larger step.

Clicking a battlemap position requests movement for the currently selected
token.

All requests still pass through the same server policy.

## Security

Movement and refresh endpoints:

- require a logged-in WordPress user
- require a Tabletop nonce
- derive viewer identity from `get_current_user_id()`
- never accept a user ID supplied by the browser
- apply Table membership and token-control rules server-side

## No WebSockets yet

IV.8 deliberately does not introduce WebSockets or Socket.IO.

Phase IV.9 can improve synchronisation transport after this authoritative
mutation model is certified.
