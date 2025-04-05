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


// small simulation
const centerLat = 50.8793;
const centerLng = 4.7002;
function assignCamp(lat, lng) {
    if (lat >= centerLat && lng >= centerLng) return {owner: 'Tuur Colignon', color: 'red'};
    if (lat >= centerLat && lng < centerLng) return {owner: 'Lowie Declerq', color: 'blue'};
    if (lat < centerLat && lng < centerLng) return {owner: 'Oscar Mues', color: 'yellow'};
    return {owner: 'Pieter Nouwen', color: 'green'};
}


function drawHexagons(hexLayer, bounds, clickable = true) {
    let hexagons = generateHexagonGrid(bounds); // generate hexagon grid
    let polygons = []; // Store references to the polygons to update later
    for (let hex of hexagons) {
        const center = findCenter(hex);
        const camp = assignCamp(center.lat, center.lng);

        let polygon = L.polygon(hex, {
            color: (clickable) ? camp.color : '#fc5200',
            weight: 1,
            opacity: 0.3,
            fillColor: (clickable) ? camp.color : '#fecab1',
            fillOpacity: (clickable) ? 0.2 : 0
        }).addTo(hexLayer);

        polygons.push({
            polygon: polygon,
            coords: hex,
            color: camp.color,
            owner: camp.owner,
            level: 1
        });
    }

    if (clickable) {
        polygons.forEach(poly => {
            poly.polygon.on('click', function(e) {
                // create pop up for each hexagon when clicked on
                openHexagonInfo(findCenter(poly.coords), poly)
            })
            poly.polygon.on('mouseover', function () {
                this.setStyle({ fillOpacity: 0.5, fillColor: '#000000' });
            });
            poly.polygon.on('mouseout', function () {
                this.setStyle({ fillOpacity: 0.2, fillColor: poly.color});
            });
        })
    }
    return polygons; // Return the list of polygons to the maps
}

function findCenter(coords) {
    return {
        'lat': coords[0][0],
        'lng': coords[0][1] - hexRadiusLng
    };
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



