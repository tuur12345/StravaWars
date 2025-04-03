const hexRadiusMeters = 150; // radius of one hexagon
const metersToLat = 1 / 111320; // conversion from degrees to meters, one degree is around 111,32 km
const metersToLng = metersToLat / Math.cos(50.875 * Math.PI / 180); // one degree is around 70,22 km

const hexRadiusLat = hexRadiusMeters * metersToLat; // radius in terms of degrees
const hexRadiusLng = hexRadiusMeters * metersToLng;

const worldBounds = { // world bounds so hexagon grid are always matching
    south: 50.77,
    north: 51,
    west: 4.58,
    east: 4.83
};

const leuvenBounds = { // visualize it to leuven for now
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

    map.whenReady(drawHexagons(hexLayer)); // draw hexagons when map is ready



});

function drawHexagons(hexLayer) {
    let hexagons = generateHexagonGrid(); // generate hexagon grid
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
    hexagons= generateHexagonGrid(); // generate hexagon grid
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

function generateHexagonGrid() {
    let hexagons = [];
    let row = 0;

    for (let lat = worldBounds.south; lat < worldBounds.north; lat += hexRadiusLat * Math.sqrt(3)/2, row++) { // calculate relative position of hexagons

        let lngOffset = (row % 2 === 0) ? 0 : hexRadiusLng * 1.5;  // shift every other row

        for (let lng = worldBounds.west + lngOffset; lng < worldBounds.east; lng += hexRadiusLng * 3) {
            if (leuvenBounds.south < lat && lat < leuvenBounds.north && leuvenBounds.west < lng && lng < leuvenBounds.east) {
                hexagons.push(generateHexagon(lat, lng)); // add hexagon
            }
        }
    }
    return hexagons;
}

function generateHexagon(centerLat, centerLng) {
    let points = [];
    for (let i = 0; i < 6; i++) { // draw six corners of each hexagon
        let angle = (Math.PI / 3) * i;
        let lat = centerLat + hexRadiusLat * Math.sin(angle);
        let lng = centerLng + hexRadiusLng * Math.cos(angle);
        points.push([lat, lng]);
    }
    return points;
}
