# Phase IV.32.6 — The Door to the Table

The Tabletop now has an intentional signed-out entrance instead of falling back to a plain chamber notice.

The Door is a presentation layer around the existing WordPress authentication contract. It uses the packaged Pippin cartographer artwork as the scenic threshold, the pixel Pippin portrait as the field-note voice, and the same brass/parchment/ink vocabulary established across the Tabletop shell.

A signed-out visitor is offered a single **Enter the Tabletop** action. Authentication still belongs to WordPress through `wp_login_url()`; this phase does not collect credentials, duplicate Companion authentication, create users, or introduce a second login system. The current request URI is carried as the WordPress login return target so Table and invitation query parameters survive the round trip.

The ordinary Tabletop masthead is suppressed while the Door is present so the entrance reads as one cinematic composition. Responsive treatment collapses the scenic/art and entry panel into a vertical threshold on smaller screens, keyboard focus remains explicit, and reduced-motion users do not depend on movement for feedback.

## Authority boundary

This phase changes only the signed-out presentation. Table membership, invitation acceptance, Table selection, Scene state, Companion identity, and WordPress authentication authority are unchanged.
