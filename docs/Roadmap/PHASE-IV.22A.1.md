# Phase IV.22A.1 — The Anchored Veil

The first Fog implementation mixed browser-rendered grid pixels with the native
dimensions of uploaded battlemat images. A grid calibrated visually at 35px
could therefore be treated as 35px on a much larger original image, placing
party vision in the wrong cells and leaving Player Preview completely veiled.

Grid calibration now records the rendered battlefield width used by the DM.
Server Fog converts that calibration to intrinsic image coordinates, while the
client converts it back to each viewer's current rendered width. Existing
Scenes require one new **Save Grid** action to establish this anchor.

Dungeon Master **Preview Player Fog** is session-persistent so Encounter reloads
such as End Turn no longer appear to reset Fog preview.

This corrective phase also hardens death-save presentation. Natural-20 recovery
was already persisted correctly at 1 HP by the server; the static DOWN panel was
not being rebuilt by the lightweight Chamber refresh. The client now synchronizes
the death-save HUD from the authoritative result, removing DOWN immediately on
revival and updating Stable/Deceased outcomes in place.
