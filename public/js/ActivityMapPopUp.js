function initializeActivityMap(div) {
    const map = L.map(div, {
        preferCanvas: true,
        zoomControl: false,
        dragging: false,
        scrollWheelZoom: false,
        doubleClickZoom: false,
        boxZoom: false,
        keyboard: false,
        tap: false,
        touchZoom: false
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    map.whenReady(() => {
        let hexLayer = L.layerGroup().addTo(map);
        let bounds = map.getBounds().pad(0.1);
        let polygons = drawHexagons(hexLayer, bounds, false);
        highlightHexagons(decodedCoords, polygons);
    });

    const route = L.layerGroup().addTo(map);
    const polylineStr = div.getAttribute('data-polyline');
    const decodedCoords = polyline.decode(polylineStr);
    const polylineLine = L.polyline(decodedCoords, { color: 'black' }).addTo(route);

    map.fitBounds(polylineLine.getBounds());
    div._leaflet_map = map;
    div._leaflet_polyline = polylineLine;
}
