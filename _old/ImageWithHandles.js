/**
 * stellt eine exemplarische Funktion für das Skalieren und Bearbeiten eines ImageOverlays auf der Karte
 * zur Verfügung.
 * Funktioniert aber noch nicht ganz, wie gewünscht.
 *
 * @param map
 * @param imageUrl
 * @param dbExtent
 * @param options
 * @returns {(function(): void)|*}
 */


// enableInteractiveStaticImage(canvasOverlay) für reines JS (keine ES-Imports).
// Benutzung: enableInteractiveStaticImage(map, "bild.png", dbExtent, { onChange, pivotMode, handleKeys })

function enableInteractiveStaticImage(map, imageUrl, dbExtent, options = {}) {
    const pivotMode = options.pivotMode || "center"; // "center" | "corner"
    const handleKeys = options.handleKeys || { rotate: "Shift", scale: "Alt" };
    const onChange = options.onChange;

    const img = new Image();
    img.src = imageUrl;

    // Canvas overlay
    const canvas = document.createElement("canvas");
    canvas.style.position = "absolute";
    canvas.style.left = "0";
    canvas.style.top = "0";
    canvas.style.pointerEvents = "none";
    map.getViewport().appendChild(canvas);

    const ctx = canvas.getContext("2d");

    function resize() {
        const size = map.getSize();
        canvas.width = size[0];
        canvas.height = size[1];
    }
    resize();
    map.on("change:size", resize);

    // init from DB extent (EPSG:3857)
    const minX = dbExtent[0], minY = dbExtent[1], maxX = dbExtent[2], maxY = dbExtent[3];
    const baseW = maxX - minX;
    const baseH = maxY - minY;

    const pivot =
        pivotMode === "corner"
            ? [minX, minY]
            : [(minX + maxX) / 2, (minY + maxY) / 2];

    const state = {
        pivot: pivot.slice(),
        translation: [0, 0],
        rotation: 3.14, // radians
        scale: 1,
        baseW,
        baseH
    };

    let dragging = false;
    let mode = null; // "move" | "rotate" | "scale"
    let start = null;

    function pixelToCoord(pixel) {
        return map.getCoordinateFromPixel(pixel);
    }
    function coordToPixel(coord) {
        return map.getPixelFromCoordinate(coord);
    }
    function angleOf(dx, dy) {
        return Math.atan2(dy, dx);
    }
    function hypot(dx, dy) {
        return Math.hypot(dx, dy);
    }

    function computeCornersWorld() {
        const hw = state.baseW / 2;
        const hh = state.baseH / 2;

        const cornersLocal = [
            [-hw, -hh],
            [ hw, -hh],
            [ hw,  hh],
            [-hw,  hh]
        ];

        const ang = state.rotation;
        const s = state.scale;
        const px = state.pivot[0] + state.translation[0];
        const py = state.pivot[1] + state.translation[1];

        const cos = Math.cos(ang);
        const sin = Math.sin(ang);

        function rot(x, y) {
            return [x * cos - y * sin, x * sin + y * cos];
        }

        return cornersLocal.map(([x, y]) => {
            const [xr, yr] = rot(x * s, y * s);
            return [px + xr, py + yr];
        });
    }

    function computeAABBFromCorners(corners) {
        let mnX = Infinity, mnY = Infinity, mxX = -Infinity, mxY = -Infinity;
        for (const [x, y] of corners) {
            if (x < mnX) mnX = x;
            if (y < mnY) mnY = y;
            if (x > mxX) mxX = x;
            if (y > mxY) mxY = y;
        }
        return [mnX, mnY, mxX, mxY];
    }

    function computeCornersPx() {
        const cornersWorld = computeCornersWorld();
        return cornersWorld.map(coordToPixel);
    }

    function hitTest(pixel) {
        const cornersPx = computeCornersPx();
        const [minPx, minPy, maxPx, maxPy] = computeAABBFromCorners(cornersPx);
        const x = pixel[0], y = pixel[1];
        return x >= minPx && x <= maxPx && y >= minPy && y <= maxPy;
    }

    function render() {
        if (!img.complete) return;

        ctx.clearRect(0, 0, canvas.width, canvas.height);

        const cornersPx = computeCornersPx();

        // cornersPx: [p0,p1,p2,p3]
        const p0 = cornersPx[0];
        const p1 = cornersPx[1];
        const p3 = cornersPx[3];

        const ux = p1[0] - p0[0];
        const uy = p1[1] - p0[1];
        const vx = p3[0] - p0[0];
        const vy = p3[1] - p0[1];

        const w = img.naturalWidth || img.width;
        const h = img.naturalHeight || img.height;

        // Map local image coords (0..w,0..h) to parallelogram in pixel space:
        const a = ux / w;
        const b = uy / w;
        const c = vx / h;
        const d = vy / h;
        const e = p0[0];
        const f = p0[1];

        ctx.save();
        ctx.setTransform(a, b, c, d, e, f);
        ctx.imageSmoothingEnabled = true;
        ctx.drawImage(img, 0, 0, w, h);
        ctx.restore();

        // border
        ctx.save();
        ctx.strokeStyle = "rgba(0,0,0,0.6)";
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.moveTo(cornersPx[0][0], cornersPx[0][1]);
        ctx.lineTo(cornersPx[1][0], cornersPx[1][1]);
        ctx.lineTo(cornersPx[2][0], cornersPx[2][1]);
        ctx.lineTo(cornersPx[3][0], cornersPx[3][1]);
        ctx.closePath();
        ctx.stroke();
        ctx.restore();

        // handles (simple)
        const pivotPx = coordToPixel(state.pivot);
        const color = mode === "rotate" ? "orange" : mode === "scale" ? "lime" : "dodgerblue";

        ctx.save();
        ctx.fillStyle = color;
        ctx.beginPath();
        ctx.arc(pivotPx[0], pivotPx[1], 6, 0, Math.PI * 2);
        ctx.fill();

        ctx.fillStyle = "#fff";
        ctx.strokeStyle = color;
        ctx.lineWidth = 2;

        for (const [x, y] of cornersPx) {
            ctx.beginPath();
            ctx.arc(x, y, 6, 0, Math.PI * 2);
            ctx.fill();
            ctx.stroke();
        }
        ctx.restore();
    }

    let raf = 0;
    function schedule() {
        if (raf) return;
        raf = requestAnimationFrame(() => {
            raf = 0;
            render();
        });
    }

    function pointerDown(evt) {
        if (!img.complete) return;
        if (!hitTest(evt.pixel)) return;

        const rotateKey = handleKeys.rotate;
        const scaleKey = handleKeys.scale;

        const orig = evt.originalEvent || {};
        const wantRotate = rotateKey && orig[rotateKey.toLowerCase ? rotateKey.toLowerCase() : rotateKey];
        // Robust handling: map specific keys
        const wantRotate2 = (rotateKey === "Shift" && orig.shiftKey) || (rotateKey === "Alt" && orig.altKey);
        const wantScale2  = (scaleKey === "Shift" && orig.shiftKey) || (scaleKey === "Alt" && orig.altKey);

        mode = wantRotate2 ? "rotate" : wantScale2 ? "scale" : "move";

        const pivotPx = coordToPixel(state.pivot);

        start = {
            mode,
            startPixel: evt.pixel.slice(),
            startRotation: state.rotation,
            startScale: state.scale,
            startTranslation: state.translation.slice(),
            startAngle: angleOf(evt.pixel[0] - pivotPx[0], evt.pixel[1] - pivotPx[1]),
            startDist: hypot(evt.pixel[0] - pivotPx[0], evt.pixel[1] - pivotPx[1])
        };

        dragging = true;
        map.getTargetElement().style.cursor =
            mode === "move" ? "grabbing" : mode === "rotate" ? "crosshair" : "nwse-resize";
    }

    function pointerDrag(evt) {
        if (!dragging || !start) return;

        const pivotPx = coordToPixel(state.pivot);

        if (start.mode === "move") {
            const c0 = pixelToCoord(start.startPixel);
            const c1 = pixelToCoord(evt.pixel);
            state.translation = [
                start.startTranslation[0] + (c1[0] - c0[0]),
                start.startTranslation[1] + (c1[1] - c0[1])
            ];
        } else if (start.mode === "rotate") {
            const ang = angleOf(evt.pixel[0] - pivotPx[0], evt.pixel[1] - pivotPx[1]);
            state.rotation = start.startRotation + (ang - start.startAngle);
        } else if (start.mode === "scale") {
            const dist = hypot(evt.pixel[0] - pivotPx[0], evt.pixel[1] - pivotPx[1]);
            const ratio = dist / Math.max(1e-6, start.startDist);
            state.scale = start.startScale * ratio;
        }

        schedule();
    }

    function pointerUp() {
        if (!dragging) return;
        dragging = false;
        mode = null;
        start = null;
        map.getTargetElement().style.cursor = "";
        schedule();

        const cornersWorld = computeCornersWorld();
        const aabb = computeAABBFromCorners(cornersWorld);

        if (typeof onChange === "function") {
            onChange({
                extentAABB: aabb, // [minX,minY,maxX,maxY]
                pivot: state.pivot.slice(),
                rotation: state.rotation,
                scale: state.scale,
                translation: state.translation.slice()
            });
        }
    }

    function onKeyFixInit() {
        // nothing; placeholder to keep pure JS simple
    }

    img.onload = () => schedule();
    schedule();

    map.on("pointerdown", pointerDown);
    map.on("pointerdrag", pointerDrag);
    map.on("pointerup", pointerUp);

    return function destroy() {
        map.un("pointerdown", pointerDown);
        map.un("pointerdrag", pointerDrag);
        map.un("pointerup", pointerUp);
        map.un("change:size", resize);
        if (canvas.parentNode) canvas.parentNode.removeChild(canvas);
    };
}

const destroy = enableInteractiveStaticImage(
    map_{{ map.id }},
    "https://arnsdorf.ddev.site/files/content-arnsdorf/karten/hofstellen/1-24HK14GUEF-Hofstellen-max_modifiziert_web.png",
    [1542151.8770948723, 6696720.983833049, 1543389.9165875609, 6698067.912431462],
    {
        pivotMode: "center",
        handleKeys: { rotate: "Shift", scale: "Alt" },
        onChange: (data) => {
            console.log("new AABB extent:", data.extentAABB);
            // in DB speichern
        }
    }
);
