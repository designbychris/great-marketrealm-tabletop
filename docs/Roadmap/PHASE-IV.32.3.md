# Phase IV.32.3 — Adventurers in Miniature

## Purpose

Carry the Tabletop's established 16-bit language into the live-play surfaces that tell the group who is at the Table, whose turn it is, and where the active Player or Keeper should act.

This phase is **presentation-only**. It does not change initiative, turn authority, targeting, damage, HP, conditions, movement, Fog or persistence.

## The party becomes a 16-bit roster

The Gathering keeps the already-certified user-avatar / Companion-character-seat split, but now treats each seated character as a miniature party record. Fellowship colour remains the ownership accent, HP becomes a deliberately pixel-segmented vitality track, and the Companion whose battlefield token owns the current turn receives a compact `TURN` flag and stronger fellowship-coloured frame.

The initial PHP render and Living Table refresh both project the same `data-party-character-id` identity. A tiny client-side synchroniser compares that identity with the active battlefield token's existing authoritative `data-token-source`; it does not invent or advance turn state.

## The Turn of Battle becomes a status plaque

The encounter strip is restyled as a compact battle plaque. Encounter state, round and current combatant remain the same authoritative values and the existing Keeper `End Turn` / `End Encounter` actions are untouched.

## Player and Keeper battle commands

The existing `YOUR TURN` badges, Satchel battle surface, highlighted Bestiary instance and Bestiary rail turn marker gain one shared pixel grammar. The active Bestiary instance remains the existing command mount for creature actions; Players still act from the existing Adventurer's Satchel.

## Battlefield miniature cursor

The existing `.is-active-turn` token state receives a square stepped cursor around the circular token. The cursor is decoration only: it does not alter token dimensions, drag bounds, ownership rings or targeting geometry.

## Accessibility and motion

Existing text labels and ARIA semantics remain authoritative. Turn state is never communicated by colour alone: `TURN`, `YOUR TURN`, round text and current-combatant text remain visible. Reduced-motion users receive no additional animation.

## Certification

Automated regressions protect the battle-plaque styling, Gathering identity/turn synchronisation, live-refresh identity, token/Bestiary turn grammar and presentation-only boundary. Browser certification is still required before the phase is closed.
