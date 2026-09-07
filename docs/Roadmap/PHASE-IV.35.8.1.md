# Phase IV.35.8.1 — The Ottoman Regains Its Colour

The first real administrator SVG exposed an overly strict presentation rule:
Inkscape commonly stores safe fill/stroke paint in inline `style` attributes.
IV.35.8 correctly removed the style attribute, but consequently converted the
multi-colour Suspicious Ottoman into one default-black silhouette.

The sanitizer now promotes only an explicit safe paint vocabulary (`fill`,
`stroke`, opacity, line cap/join and stroke width) from inline style
declarations into normal SVG attributes. The style attribute itself remains
forbidden, along with URL references, scripts, event handlers and active CSS.

Existing already-sanitised custom sprites must be re-uploaded once so the
original colour information can be recovered from the source SVG.

> Pippin: “It was less suspicious when it was black.”
