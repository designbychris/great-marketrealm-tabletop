# Phase IV.23 — The Gathering at the Table

Status: development candidate

The Tabletop now treats the people at a Table as first-class identities rather than numeric placeholders.

## Scope

- Project persistent Table membership through real WordPress display names and avatars.
- Keep user identity, Table membership and character assignment as separate concepts.
- Let the Dungeon Master invite an existing WordPress user by username, email or user ID.
- Let an invited player explicitly take their seat before Chamber access becomes active.
- Keep invitation and membership changes server-authoritative.
- Surface Great Marketrealm Companion availability through the existing integration boundary without importing Companion internals into Tabletop or Tables domains.
- Preserve the opaque `companion_character_id` seam for the next character-selection/import pass.

## Browser certification

- [ ] Dungeon Master appears by real display name/avatar rather than `User #ID`.
- [ ] Inviting an existing WordPress user produces an invited member in the Gathering roster.
- [ ] Invited user sees `Take My Seat` and can accept the invitation.
- [ ] Accepted player enters the same Table as an active Player member.
- [ ] Companion connection status is shown without making GMRC mandatory.
