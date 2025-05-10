function openHexagonInfo(hexagon) {
    const container = L.DomUtil.create('div');

    container.innerHTML = createInnerHTML(hexagon);

    if (user === hexagon.owner) {
        addUpgradeButton(container, hexagon);
    }

    createHexagonPopUp(container, hexagon);
}

function createInnerHTML(hexagon) {
    let latlng = findCenter(hexagon.coords);
    return `
        <strong>Hexagon Info</strong><br>
        Center: [${latlng.lat.toFixed(4)}, ${latlng.lng.toFixed(4)}]<br>        Owner: ${hexagon.owner || 'Not assigned'}<br>
        Color: ${hexagon.color}<br>
        Level: <span id="hex-level">${hexagon.level}</span><br>
    `
}

function addUpgradeButton(container, hexagon) {
    const button = L.DomUtil.create('button', '', container);
    button.textContent = ' Upgrade';
    button.style.backgroundImage = 'url("/images/stravabucks.png")';
    button.style.backgroundRepeat = 'no-repeat';
    button.style.backgroundPosition = '8px center';
    button.style.paddingLeft = '32px';
    button.style.backgroundSize = '16px';

    L.DomEvent.on(button, 'click', () => {
        hexagon.level++;
        container.querySelector('#hex-level').textContent = hexagon.level;
    });
}

function createHexagonPopUp(container, hexagon) {
    L.popup({ className: 'hex-popup' })
        .setLatLng(findCenter(hexagon.coords))
        .setContent(container)
        .openOn(hexagon.polygon._map);
}

function openHexagonAttackInfo(hexagon) {
    const container = L.DomUtil.create('div');

    container.innerHTML = createInnerHTML(hexagon);

    addUpgradeButton(container, hexagon);

    createHexagonPopUp(container, hexagon);
}