function openHexagonInfo(latlng, hexagon) {
    const container = L.DomUtil.create('div');

    container.innerHTML = `
        <strong>Hexagon Info</strong><br>
        Center: [${latlng.lat.toFixed(4)}, ${latlng.lng.toFixed(4)}]<br>        Owner: ${hexagon.owner || 'Not assigned'}<br>
        Color: ${hexagon.color}<br>
        Level: <span id="hex-level">${hexagon.level}</span><br>
    `;
    if (user === hexagon.owner) {
        const button = L.DomUtil.create('button', '', container);
        button.textContent = 'Upgrade';

        L.DomEvent.on(button, 'click', () => {
            hexagon.level++;
            container.querySelector('#hex-level').textContent = hexagon.level;
        });
    }


    L.popup({ className: 'hex-popup' })
        .setLatLng(latlng)
        .setContent(container)
        .openOn(hexagon.polygon._map);

}