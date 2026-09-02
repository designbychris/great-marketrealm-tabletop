# Phase IV.32.6A — The Door Has a Lock

## Intent
Give the Tabletop its own front-end authentication threshold without creating a second identity system. WordPress remains authoritative for credentials, auth cookies and current-user identity.

## Delivered
- Full-bleed Pippin dungeon artwork beneath a compact pixel/brass sign-in card.
- Native Tabletop username/email + password form with password-manager autocomplete and Remember Me.
- Dedicated nonce verification and `wp_signon()` authentication handled on `template_redirect`, before page output.
- Safe redirect back to the exact requested Table/invitation URL after successful authentication.
- Inline accessible failure messages without exposing WordPress authentication internals.
- Cinematic `Your chair is waiting` threshold for authenticated invitees.
- Invitation acceptance remains explicit and separate via `Take My Seat`.
- Responsive, keyboard-focus and reduced-motion presentation safeguards.

## Authority Boundary
The Tabletop owns presentation only. WordPress remains the authentication authority; Table membership remains owned by the existing invitation/membership services.

## Browser Certification
1. Open an invitation URL while signed out and confirm the full-bleed Door login is shown.
2. Submit invalid credentials and confirm a readable inline error remains on the Door.
3. Submit valid Companion/WordPress credentials and confirm the browser returns to the same invitation/Table URL.
4. Confirm the signed-in `Your chair is waiting` threshold is cinematic and `Take My Seat` still performs explicit membership acceptance.
5. Confirm direct non-invitation Table URLs preserve their requested destination through login.
