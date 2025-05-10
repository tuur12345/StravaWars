document.addEventListener("DOMContentLoaded", function () { // when map div is loaded
    const map = createWorldMap();
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

    addWorldHexagons(map);
    //insert_hexagon_into_database(map, bounds);
});

function createWorldMap() {
    return L.map('map', {
        preferCanvas: true
    }).setView([50.875, 4.7], 14); // map centered at leuven, zoomed in
}

async function addWorldHexagons(map) {
    let hexagonLayer = L.layerGroup().addTo(map); // add a hexagonLayer to the map
    let hexagons = await drawHexagons(hexagonLayer);
    addClickListenerWorldMap(hexagons);
}

function addClickListenerWorldMap(hexagons) {
    hexagons.forEach(hex => {
        hex.polygon.on('click', function () {
            openHexagonInfo(hex)
        })
        addMouseListener(hex);
    })
}

// function insert_hexagon_into_database(map, bounds) {
//     let hexagonLayer = L.layerGroup().addTo(map); // add a hexagonLayer to the map
//     map.whenReady(async function() {
//         let polygons = await drawHexagons(hexagonLayer);
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
// }