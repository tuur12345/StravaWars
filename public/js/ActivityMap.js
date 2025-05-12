// wait on DOM to be fully loaded since the polyline takes some time
document.addEventListener("DOMContentLoaded", function () {
    const activities = document.querySelectorAll('.activity-map');
    activities.forEach((activityMap) => {
        initializeActivityMap(activityMap)
    });
});

function initializeActivityMap(div, clickable = false) {
    const map = createMap(div, clickable);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

    let polylineCoords = addRoute(div, map);

    addHexagons(map, clickable, polylineCoords);

    div._leaflet_map = map; // store map in div
}

function createMap(div, clickable) {
    return L.map(div, {
        preferCanvas: true,
        zoomControl: clickable,
        dragging: clickable,
        scrollWheelZoom: clickable,
        doubleClickZoom: clickable,
        boxZoom: clickable,
        keyboard: clickable,
        tap: clickable,
        touchZoom: clickable
    });
}


function addRoute(div, map) {
    const route = L.layerGroup().addTo(map);
    const polylineStr = div.getAttribute('data-polyline');
    const polylineCoords = polyline.decode(polylineStr);
    const polyLine = L.polyline(polylineCoords, { color: '#fc5200' }).addTo(route);

    map.fitBounds(polyLine.getBounds());
    div._leaflet_polyline = polyLine;

    return polylineCoords;
}

async function addHexagons(map, clickable, polylineCoords) {
    let hexLayer = L.layerGroup().addTo(map);
    let hexagons = await drawHexagons(hexLayer);
    if (clickable) {
        addClickListenerActivityMap(hexagons, polylineCoords);
    }
}

function addClickListenerActivityMap(hexagons, polylineCoords) {
    hexagons.forEach(hex => {
        hex.polygon.on('click', function () {
            if (hexagonInPolyline(hex, polylineCoords)) {
                openHexagonAttackInfo(hex)
            } else {
                openHexagonInfo(hex);
            }

        })
        addMouseListener(hex);
    })
}

function hexagonInPolyline(hex, coords) {
    for (let coord of coords) {
        if (pointInHexagon(coord, hex.coords)) { // check if point is inside the hexagon
            return true;
        }
    }
    return false;
}


function pointInHexagon(point, hexCoords) {
    // made by chatgpt
    let [x, y] = point;
    let inside = false;
    let j = hexCoords.length - 1;

    for (let i = 0; i < hexCoords.length; i++) {
        let xi = hexCoords[i][0], yi = hexCoords[i][1];
        let xj = hexCoords[j][0], yj = hexCoords[j][1];

        let intersect = ((yi > y) !== (yj > y)) &&
            (x < (xj - xi) * (y - yi) / (yj - yi) + xi);

        if (intersect) inside = !inside;
        j = i;
    }

    return inside;
}