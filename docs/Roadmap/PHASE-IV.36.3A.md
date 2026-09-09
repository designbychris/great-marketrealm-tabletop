# Phase IV.36.3A — The Old Signpost Was Still Correct

IV.36.3 added secret metadata to the existing Forge projection, but unnecessarily
incremented the projection version from 4 to 5. The established regression contract
correctly caught this.

Secrets are additive optional fields and do not require a breaking projection schema
version. This corrective pass restores version 4 while retaining the complete
IV.36.3 secret/reveal behaviour.

No new regression is required: the existing Forge interior-design regression is the
contract that detected the mistake. Expected suite remains 1,140 tests.
