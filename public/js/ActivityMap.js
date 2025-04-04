document.addEventListener("DOMContentLoaded", function () {
    const activities = document.querySelectorAll('.activity-map');
    activities.forEach((activityMap) => {
        const map = L.map(activityMap.id, {
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
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);



        map.whenReady(() => {
            let hexLayer = L.layerGroup().addTo(map);
            let bounds = map.getBounds().pad(0.1);
            let polygons = drawHexagons(hexLayer, bounds);
                highlightHexagons(decodedCoords, polygons);
        });
        const route = L.layerGroup().addTo(map);
        const polylineStr = activityMap.getAttribute('data-polyline');
        const decodedCoords = polyline.decode(polylineStr);
        const polylineLine = L.polyline(decodedCoords, { color: 'black' }).addTo(route);
        map.fitBounds(polylineLine.getBounds());
    });
});
