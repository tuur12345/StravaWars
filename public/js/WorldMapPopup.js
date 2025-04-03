document.addEventListener("DOMContentLoaded", function () {
    const activities = document.querySelectorAll('.activities-map');
    activities.forEach((activityMap) => {
        const map = L.map(activityMap.getAttribute("id"), { preferCanvas: true });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        let route = L.layerGroup().addTo(map);
        const polylineStr = activityMap.getAttribute('data-polyline');

        const decodedCoords = polyline.decode(polylineStr);
        const polylineLine = L.polyline(decodedCoords, { color: 'blue' }).addTo(route);
        map.fitBounds(polylineLine.getBounds());

        let hexLayer = L.layerGroup().addTo(map); // add a hexlayer to the map

        map.whenReady(function() { // draw hexagons when map is ready
            drawHexagons(hexLayer, map.getBounds().pad(0.1));
        });
    });
});