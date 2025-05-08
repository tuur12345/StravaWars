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

    map.whenReady(/*async*/ function() { // draw hexagons when map is ready
        drawHexagons(hexLayer, bounds);


        // only used to insert hexagons in database
        // let polygons = await drawHexagons(hexLayer, bounds);
        //
        // let hexagons = polygons.map(poly => {
        //     let center = findCenter(poly.coords);
        //     return {
        //         latitude: center.lat,
        //         longitude: center.lng,
        //         color: poly.color
        //     };
        // });
        //
        // fetch('/insert-hexagons', {
        //     method: 'POST',
        //     headers: {'Content-Type': 'application/json'},
        //     body: JSON.stringify({hexagons: hexagons})
        // })
        //     .then(response => response.json())
        //     .then(data => console.log('Success:', data))
        //     .catch(error => console.error('Error:', error));

    });
});