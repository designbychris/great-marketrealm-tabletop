# Phase IV.4 — The Gathering of Adventurers

## Purpose

Give every Table a persistent gathering of Dungeon Masters and Players
without duplicating WordPress accounts or Great Marketrealm Companion
Characters.

## Table membership

A Table membership relates:

- one GMRT Table ID
- one WordPress user ID
- one Table role
- one membership status
- optionally one opaque Companion Character ID

The Tabletop does not copy Character names, stats, spells, portraits, HP,
equipment, or other Companion-owned Character data into the membership
record.

## Roles

Initial Table roles are:

- Dungeon Master
- Player

The Dungeon Master is automatically seated as an Active member when a
Table is prepared through the production Table Registry.

## Player lifecycle

Players use the lifecycle:

**Invited → Active → Left**

A player must be invited before joining.

A player who leaves may later be invited again.

The Dungeon Master cannot leave their own Table through the player
membership workflow.

## Permissions

An Active Dungeon Master may manage and participate in the Table.

An Active Player may participate but may not manage the Table.

Invited and Left players are not active participants.

## Companion Character seam

An Active member may select an opaque Companion Character reference.

This phase deliberately does not ask GMRC for the Character record. That
will happen through a stable cross-plugin integration contract in a later
phase.

The important ownership rule is already enforced:

**GMRT stores the selected Character ID. GMRC owns the Character.**

## Ended Tables

Ended Tables retain historical membership records but reject new
invitations, joins, and Character selection changes.

## Non-goals

IV.4 does not yet implement:

- invitation URLs or email delivery
- lobby UI
- Character chooser UI
- Character ownership validation against GMRC
- portraits/tokens
- spectators
- maps
- live networking
- reconnection transport

Those systems now have a stable membership model to build upon.
