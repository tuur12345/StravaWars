function highlightHexagons(coords, polygons) {
    for (let coord of coords) {
        for (let poly of polygons) {
            if (pointInPolygon(coord, poly.coords)) { // check if point is inside the hexagon
                poly.polygon.setStyle({ fillColor: '#fc5200', fillOpacity: 0.4 });
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