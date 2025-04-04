const hexRadiusMeters = 150; // radius of one hexagon
const metersToLat = 1 / 111320; // conversion from degrees to meters, one degree is around 111,32 km
const metersToLng = metersToLat / Math.cos(50.875 * Math.PI / 180); // one degree is around 70,22 km

const hexRadiusLat = hexRadiusMeters * metersToLat; // radius in terms of degrees
const hexRadiusLng = hexRadiusMeters * metersToLng;

const worldBounds = { // world bounds so hexagon grid are always matching
    south: 50.5,
    north: 51.5,
    west: 2,
    east: 6
};

function drawHexagons(hexLayer, bounds, clickable = true) {
    let hexagons = generateHexagonGrid(bounds); // generate hexagon grid
    let polygons = []; // Store references to the polygons to update later
    for (let hex of hexagons) { // customize the hexagons
        let polygon = L.polygon(hex, {
            color: 'blue',
            weight: 1,
            opacity: 0.3,
            fillColor: 'blue',
            fillOpacity: 0.2
        }).addTo(hexLayer);

        if (clickable) {
            polygon.on('click', function (e) {
                let newColor = this.options.fillColor === 'blue' ? 'red' : 'blue'; // play with color
                this.setStyle({ fillColor: newColor });
                alert("hexagon pushed at: " + hex);
            });
        }


        polygons.push({ // store polygon and other variables
            polygon: polygon,
            coords: hex,
            owner: null,
            color: 'blue',
            level: 1
        });
    }
    return polygons; // Return the list of polygons
}

function generateHexagonGrid(bounds) {
    let hexagons = [];
    let row = 0;
    // calculate relative position of hexagons
    for (let lat = worldBounds.south; lat < worldBounds.north; lat += hexRadiusLat * Math.sqrt(3) / 2, row++) {
        // shift every other row
        let lngOffset = (row % 2 === 0) ? 0 : hexRadiusLng * 1.5;

        for (let lng = worldBounds.west + lngOffset; lng < worldBounds.east; lng += hexRadiusLng * 3) {
            if (bounds.contains([lat, lng])) {
                hexagons.push(generateHexagon(lat, lng));
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

function highlightHexagons(coords, polygons) {
    for (let coord of coords) {
        for (let poly of polygons) {
            if (pointInPolygon(coord, poly.coords)) { // check if point is inside the hexagon
                poly.color = 'red';
                poly.polygon.setStyle({ fillColor: 'red' });
            }
        }
    }
}

// function to check if a point is inside a hexagon, made by chatgpt
function pointInPolygon(point, polygon) {
    let [x, y] = point;
    let inside = false;
    let j = polygon.length - 1;

    for (let i = 0; i < polygon.length; i++) {
        let xi = polygon[i][0], yi = polygon[i][1];
        let xj = polygon[j][0], yj = polygon[j][1];

        let intersect = ((yi > y) !== (yj > y)) &&
            (x < (xj - xi) * (y - yi) / (yj - yi) + xi);

        if (intersect) inside = !inside;
        j = i;
    }

    return inside;
}
