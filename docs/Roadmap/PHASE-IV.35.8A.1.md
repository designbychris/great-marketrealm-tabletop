# Phase IV.35.8A.1 — Administration Actually Files the Form

An administrator editing Forge labels could reach `wp-admin/admin-post.php`
without returning to the Furniture Catalogue and without a PHP fatal appearing
in WordPress debug logs.

The Catalogue retains the normal `admin_post_*` handlers and now also claims
its own POST actions during `admin_init`, which runs earlier in the WordPress
admin request. The fallback delegates to the exact same capability- and
nonce-protected `save()` / `delete()` handlers, so no second mutation path or
weaker security boundary is introduced.

Successful saves continue to redirect to **Tools → Furniture Catalogue** with
the existing status notice.

> Pippin: “The form has been filed.”
>
> Keeper: “Where?”
>
> Pippin: “This time, somewhere useful.”
