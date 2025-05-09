document.addEventListener("DOMContentLoaded", function () { // when map div is loaded
    const map = L.map('map', {
        preferCanvas: true
    }).setView([50.875, 4.7], 14); // map centered at leuven, zoomed in

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { // add actual map
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    let hexLayer = L.layerGroup().addTo(map); // add a hexlayer to the map

    let bounds = L.latLngBounds(
        L.latLng(50.82, 4.61),    // Southwest
        L.latLng(50.92, 4.81)   // Northeast
    );

    map.whenReady(function() { // draw hexagons when map is ready
        drawHexagons(hexLayer, bounds);
    });

    //insert_hexagon_into_database(map, hexLayer, bounds) // query to initialise hexagons in database, only used once
});

// function insert_hexagon_into_database(map, hexLayer, bounds) {
//     map.whenReady(async function() {
//         let polygons = await drawHexagons(hexLayer, bounds);
//
//         let hexagons = polygons.map(poly => {
//             let center = findCenter(poly.coords);
//             return {
//                 latitude: center.lat.toFixed(64),
//                 longitude: center.lng.toFixed(64),
//                 color: poly.color,
//                 owner: poly.owner,
//                 level: poly.level
//             };
//         });
//
//         fetch('/insert-hexagons', {
//             method: 'POST',
//             headers: {'Content-Type': 'application/json'},
//             body: JSON.stringify({hexagons: hexagons})
//         })
//             .then(response => response.json())
//             .then(data => console.log('Success:', data))
//             .catch(error => console.error('Error:', error));
//     });
//}