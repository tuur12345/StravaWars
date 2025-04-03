document.addEventListener("DOMContentLoaded", function () { // when map div is loaded
    const map = L.map('map', {
        preferCanvas: true
    }).setView([50.875, 4.7], 14); // map centered at leuven, zoomed in

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { // add actual map
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    let hexLayer = L.layerGroup().addTo(map); // add a hexlayer to the map

    let bounds = L.latLngBounds( // visualize it to leuven for now
        L.latLng(50.82, 4.59),  // Southwest
        L.latLng(50.90, 4.81)   // Northeast
    );

    map.whenReady(function() { // draw hexagons when map is ready
        drawHexagons(hexLayer, bounds)
    });
});