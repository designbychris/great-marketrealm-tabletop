# Phase IV.30.1G.5F — Playable-Space Adjacency & Threshold Connectivity

## Pippin Asks What the Wall Would Do

G.5E taught the Cartographer that a convincing ink contour can have several semantic roles. G.5F adds the next question: **what happens to the playable space if Pippin treats that contour as a wall?**

Living Contour now performs a topology-aware adjacency pass after semantic boundary-role classification. Closed chains sample playable floor inside and immediately outside their envelope. A contour surrounded by connected playable floor receives an isolation penalty and can be demoted from structural wall evidence to terrain/elevation rather than becoming an automatic line-of-sight barrier. This is aimed directly at hills, raised areas, rubble islands and similar interior marks on heavily illustrated cave maps.

Open chains also inspect their unresolved ends. When nearby ends are separated by a short gap whose intervening samples remain playable floor, the gap is classified as **threshold-connected open chain** evidence. That inferred threshold is protected from automatic bridging even if Structural tracing did not recognise a conventional door glyph.

The phase deliberately builds on, rather than replaces, G.5D chain certification and G.5E boundary roles. Structural evidence still matters; legitimate interior obstacles remain reviewable; uncertainty never grants permission to invent a wall.

### Acceptance contracts

- Playable-space adjacency runs after semantic boundary-role classification.
- Closed contours with playable floor both inside and outside can be demoted to terrain/elevation evidence.
- Short open-chain gaps with continuous playable floor are preserved as probable thresholds.
- Threshold connectivity is carried into Living Contour metadata and gap protection.
- Hybrid continues consuming the shared Living Contour semantic pipeline rather than introducing a separate topology reader.
- Existing G.5D/G.5E safety contracts remain intact.
