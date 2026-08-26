# Phase IV.3 — The Steward's Table Rules

## Purpose

Protect the Tabletop server before live maps, tokens, and real-time synchronisation begin.

## Capacity

The default remains **2 simultaneously active Tables**. Capacity is enforced when a preparing Table attempts to activate.

## Leases and heartbeats

Every Active Table receives a renewable lease. Defaults are:

- lease duration: **900 seconds / 15 minutes**
- heartbeat grace: **120 seconds / 2 minutes**

The effective default expiry window is therefore 17 minutes after the most recent accepted heartbeat. Tables persist `last_heartbeat_at` and `lease_expires_at`.

## Abandoned-session recovery

Before activating another Table, GMRT reclaims expired active Tables. An expired Table moves to **Ended** and immediately releases its capacity slot.

A heartbeat arriving after expiry does not silently resurrect the session; the Table is ended and a lease-expired exception is raised.

## Steward override

`gmrt_capacity_override_user_ids` may contain explicitly trusted Dungeon Master WordPress user IDs. It is empty by default and is intended for controlled Steward/development use.

## Safety minimums

Lease duration cannot be configured below five minutes. Heartbeat grace cannot be configured below one minute.

## Non-goals

This phase does not yet expose browser heartbeat endpoints, cron jobs, WebSockets, reconnect UI, player membership, Table administration screens, maps, or tokens.
