const hexRadiusMeters = 150; // radius of one hexagon
const metersToLat = 1 / 111320; // conversion from degrees to meters, one degree is around 111,32 km
const metersToLng = metersToLat / Math.cos(50.875 * Math.PI / 180); // one degree is around 70,22 km

const hexRadiusLat = hexRadiusMeters * metersToLat; // radius in terms of degrees
const hexRadiusLng = hexRadiusMeters * metersToLng;

const leuvenBounds = { // bound it to leuven for now
    south: 50.87,
    north: 50.90,
    west: 4.68,
    east: 4.73
};

document.addEventListener("DOMContentLoaded", function () { // when map div is loaded
    const map = L.map('map').setView([50.875, 4.7], 14); // map centered at leuven, zoomed in

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { // add actual map
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    let hexLayer = L.layerGroup().addTo(map); // add a hexlayer to the map

    map.whenReady(drawHexagons(hexRadiusLat, hexRadiusLng, hexLayer, leuvenBounds)); // draw hexagons when map is ready
});

function drawHexagons(hexRadiusLat, hexRadiusLng, hexLayer, leuvenBounds) {
    let hexagons = generateHexagonGrid(hexRadiusLat, hexRadiusLng, leuvenBounds); // generate hexagon grid
    for (let hex of hexagons) { // customize the hexagons
        let polygon = L.polygon(hex, {
            color: 'blue',
            weight: 1,
            opacity: 0.5,
            fillColor: 'blue',
            fillOpacity: 0.3
        }).addTo(hexLayer);

        polygon.on('click', function (e) {
            let newColor = this.options.fillColor === 'blue' ? 'red' : 'blue'; // play with color
            this.setStyle({ fillColor: newColor });
        });
    }
    return 0;
}

function generateHexagonGrid(sizeLat, sizeLng, leuvenBounds) {
    let hexagons = [];
    let row = 0;

    for (let lat = leuvenBounds.south; lat < leuvenBounds.north; lat += sizeLat * Math.sqrt(3)/2, row++) { // calculate relative position of hexagons

        let lngOffset = (row % 2 === 0) ? 0 : sizeLng * 1.5;  // shift every other row

        for (let lng = leuvenBounds.west + lngOffset; lng < leuvenBounds.east; lng += sizeLng * 3) {
            hexagons.push(generateHexagon(lat, lng, sizeLat, sizeLng)); // add hexagon
        }
    }
    return hexagons;
}


function generateHexagon(centerLat, centerLng, sizeLat, sizeLng) {
    let points = [];
    for (let i = 0; i < 6; i++) { // draw six corners of each hexagon
        let angle = (Math.PI / 3) * i;
        let lat = centerLat + sizeLat * Math.sin(angle);
        let lng = centerLng + sizeLng * Math.cos(angle);
        points.push([lat, lng]);
    }
    return points;
}
