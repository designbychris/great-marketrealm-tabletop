# Phase IV.10A — Tabletop Page Host Integration

## Purpose

Make the Great Marketrealm Tabletop follow the same hosting pattern as the
main Companion application: WordPress owns the Page and theme shell while the
plugin owns the application rendered inside it.

## WordPress Page

Create a normal WordPress Page with the desired slug:

`/tabletop/`

Elementor may control that Page's normal site header, footer and surrounding
layout.

## Shortcode

Place this shortcode in the Page content:

`[great_marketrealm_tabletop]`

GMRT renders the Tabletop Chamber only. It does not output its own HTML
document, header or footer.

## Table selection

The shortcode accepts an explicit Table ID:

`[great_marketrealm_tabletop table="TABLE-ID"]`

For normal navigation, the host Page may instead receive:

`/tabletop/?table=TABLE-ID`

The query value is used only to select the Table. Membership and permissions
remain server-authoritative.

## Ownership boundary

WordPress / Elementor owns:

- page slug
- header
- footer
- navigation
- surrounding layout

Great Marketrealm Tabletop owns:

- Chamber markup
- Table state
- Scenes
- Tokens
- movement
- Encounters
- authenticated AJAX
- Tabletop CSS and JavaScript

## Rewrite retirement

GMRT no longer registers a rewrite rule for `/tabletop/` and no longer
intercepts `template_redirect`.

This prevents conflict with the real WordPress Page.

## Migration

After deploying IV.10A:

1. Create or edit the WordPress Page using slug `tabletop`.
2. Add a Shortcode widget/block.
3. Insert `[great_marketrealm_tabletop]`.
4. Publish/update the Page.
5. Visit `/tabletop/`.

No permalink flush should be required by GMRT for the Tabletop Page itself.
