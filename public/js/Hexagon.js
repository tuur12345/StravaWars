const hexRadiusMeters = 150; // radius of one hexagon
const metersToLat = 1 / 111320; // conversion from degrees to meters, one degree is around 111,32 km
const metersToLng = metersToLat / Math.cos(50.875 * Math.PI / 180); // one degree is around 70,22 km

const hexRadiusLat = hexRadiusMeters * metersToLat; // radius in terms of degrees
const hexRadiusLng = hexRadiusMeters * metersToLng;

// const worldBounds = { // world bounds so hexagon grid are always matching
//     south: 50.5,
//     north: 51.5,
//     west: 2,
//     east: 6
// };
//
// const bounds = L.latLngBounds(
//     L.latLng(50.82, 4.61),    // Southwest
//     L.latLng(50.92, 4.81)   // Northeast
// );


async function drawHexagons(hexagonLayer) {
    //let hexagons_db = generateHexagonGrid(bounds);
    let hexagons = [];
    for (let hex of hexagons_db) {
        let points = generateHexagon(hex.latitude, hex.longitude); // generate 6 point based on center coordinates stored in database
        let polygon = createPolygon(hex, points, hexagonLayer); // create polygon from database

        pushHexagon(hexagons, polygon, hex, points);
    }
    return hexagons; // return the list of polygons to the maps
}

function createPolygon(hex, points, hexagonLayer) {
    return L.polygon(points, {
        color: hex.color,
        weight: 1,
        opacity: 0.2,
        fillColor: hex.color,
        fillOpacity: (hex.color !== '#fc5200') ? 0.4 : 0
    }).addTo(hexagonLayer);
}

function pushHexagon(hexagons, polygon, hex, points) {
    hexagons.push({
        polygon: polygon,
        coords: points,
        color: hex.color,
        owner: hex.owner,
        level: hex.level
    });
}

function findCenter(coords) {
    return {
        'lat': coords[0][0],
        'lng': coords[0][1] - hexRadiusLng
    };
}

function generateHexagon(centerLat, centerLng) {
    let points = [];
    for (let i = 0; i < 6; i++) { // draw six corners of each hexagon
        let angle = (Math.PI / 3) * i;
        let lat = parseFloat(centerLat) + hexRadiusLat * Math.sin(angle);
        let lng = parseFloat(centerLng) + hexRadiusLng * Math.cos(angle);
        points.push([lat, lng]);
    }
    return points;
}

function addMouseListener(hex) {
    hex.polygon.on('mouseover', function () {
            this.setStyle({
                fillOpacity: 0.6,
                fillColor: '#000000'
            });
    });
    hex.polygon.on('mouseout', function () {
        this.setStyle({
            fillOpacity: (hex.color !== '#fc5200') ? 0.2 : 0,
            fillColor: hex.color
        });
    });
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



