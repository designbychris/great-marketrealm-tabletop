# Phase IV.2 — The First Table

## Purpose

Give Great Marketrealm Tabletop its first persistent game-domain object.

## Table lifecycle

A Table begins as **Preparing**.

A preparing Table may become **Active** when capacity is available.

An active Table may become **Ended**.

Ended Tables remain persisted and are not destructively removed.

## Ownership

Every Table has one immutable Dungeon Master WordPress user ID.

Player membership is intentionally deferred to Phase IV.4.

## Capacity

Activation is guarded by a `TableCapacityPolicy`.

The WordPress policy reads `gmrt_active_table_capacity`, seeded to `2`
during IV.1 activation.

Two active Tables may coexist. Attempting to activate another Table while
both slots are occupied raises a domain-level capacity exception.

Ending a Table releases its slot immediately.

## Persistence

IV.2 stores early Table metadata through a WordPress option adapter behind
the `TableRepository` interface.

This is deliberate: gameplay code depends only on the repository contract,
so later phases may migrate Table metadata to dedicated database storage
without rewriting the domain model or Table Registry.

## Non-goals

This phase does not implement:

- player membership or invitations
- Companion Character selection
- maps or scenes
- tokens
- encounters
- initiative
- leases/heartbeats
- idle expiry
- real-time synchronisation

Those belong to later certified phases.
