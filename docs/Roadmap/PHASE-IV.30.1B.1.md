# Phase IV.30.1B.1 — Fine Contour Sampling

Fine Contour Sampling decouples the **Cartography analysis resolution** from the calibrated **gameplay grid**.

The gameplay grid remains authoritative for movement, range, tokens and map scale. Living Contour now subdivides each gameplay square into an adaptive temporary analysis mesh (up to 6×6 samples per square, reduced automatically on large maps). This lets cave-floor classification and floor/solid boundary tracing work at substantially finer resolution without asking the Keeper to mis-calibrate the Scene.

Fine contour endpoints may occupy fractional gameplay-grid coordinates. Sight Beyond the Door therefore preserves precise numeric barrier endpoints while ordinary manually drawn grid barriers continue to work exactly as before. The existing LOS resolver already operates on numeric line geometry, so fine contours remain ordinary authoritative vision barriers once explicitly accepted.

Browser work remains bounded by an analysis-cell budget, isolated fine-mesh noise is filtered before tracing, and the existing 200-suggestion review cap remains unchanged. Nothing is saved automatically: Fine Contour Sampling still produces only the Keeper's private draft until **Apply Selected**.

The regular dungeon remains the constructed-dungeon control and the complex cave remains the Living Contour benchmark. The original **misaligned-grid** benchmark is still reserved for later grid-registration work; this phase does not reinterpret or alter gameplay calibration.
