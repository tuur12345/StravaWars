function openHexagonInfo(hexagon) {
    const container = L.DomUtil.create('div');

    const popup = createHexagonPopUp(container, hexagon);

    container.innerHTML = createInnerHTML(hexagon);

    if (user === hexagon.owner) {
        addUpgradeButton(container, hexagon);
    }

    requestAnimationFrame(() => popup.update());
}

function openHexagonAttackInfo(hexagon) {
    const container = L.DomUtil.create('div');

    const popup = createHexagonPopUp(container, hexagon);

    container.innerHTML = createInnerHTML(hexagon);

    addActionButton(container, hexagon);

    requestAnimationFrame(() => popup.update());
}

function createHexagonPopUp(container, hexagon) {
    return L.popup({ className: 'hex-popup' })
        .setLatLng(findCenter(hexagon.coords))
        .setContent(container)
        .openOn(hexagon.polygon._map);
}

function createInnerHTML(hexagon) {
    let latlng = findCenter(hexagon.coords);
    return `
        <strong>Hexagon Info</strong><br>
        Center: [${latlng.lat.toFixed(4)}, ${latlng.lng.toFixed(4)}]<br>        
        Owner: <span id="hex-owner">${hexagon.owner}</span><br>
        Color: ${hexagon.color}<br>
        Level: <span id="hex-level">${hexagon.level}</span><br>
    `
}

function addActionButton(container, hexagon) {
    if (user === hexagon.owner) {
        addUpgradeButton(container, hexagon);
    } else if (hexagon.owner === "None") {
        addClaimButton(container, hexagon);
    } else {
        addAttackButton(container, hexagon);
    }
}

function addUpgradeButton(container, hexagon) {
    const button = L.DomUtil.create('button', 'hex-button upgrade-button', container);
    button.textContent = ' Upgrade';

    L.DomEvent.on(button, 'click', () => {
        hexagon.level++;
        updateHexagonInDb(container, hexagon, button)
    });
}

function addAttackButton(container, hexagon) {
    const button = L.DomUtil.create('button', 'hex-button attack-button', container);
    button.textContent = ' Attack';

    L.DomEvent.on(button, 'click', () => {
        hexagon.level--;
        if (hexagon.level === 0) {
            hexagon.color = '#fc5200';
            hexagon.owner = "None";
        }
        updateHexagonInDb(container, hexagon, button);
    });
}

function addClaimButton(container, hexagon) {
    const button = L.DomUtil.create('button', 'hex-button claim-button', container);
    button.textContent = ' Claim';

    L.DomEvent.on(button, 'click', () => {
        hexagon.level = 1;
        hexagon.color = userColor;
        console.log(hexagon.color);
        hexagon.owner = user;
        updateHexagonInDb(container, hexagon, button);
    });
}
async function updateHexagonInDb(container, hexagon, button) {
    button.disabled = true;
    button.textContent = button.textContent + ' ...';
    try {
        const response = await fetch('/public/hexagon/claim', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                latitude: findCenter(hexagon.coords).lat.toFixed(64),
                longitude: findCenter(hexagon.coords).lng.toFixed(64),
                owner: hexagon.owner,
                color: hexagon.color,
                level: hexagon.level
            })
        });

        const responseData = await response.json();

        if (response.ok) {
            hexagon.color = responseData.color;
            hexagon.owner = responseData.owner;
            hexagon.level = responseData.level;
            container.querySelector('#hex-owner').textContent = responseData.owner;
            container.querySelector('#hex-level').textContent = responseData.level;
            hexagon.polygon.setStyle({ fillColor: hexagon.color, fillOpacity: (responseData.owner !== "None") ? 0.4 : 0 });

            // Add success notification
            showNotification('Success', 'Hexagon updated successfully!', 'success');
        } else {
            // Replace alert with showNotification
            console.error("Action failed:", responseData.message);
            showNotification('Action Failed', responseData.message || "Failed to update hexagon", 'error');
        }
    } catch (error) {
        console.error("Error in updateHexagonInDb:", error);
        // Replace alert with showNotification
        showNotification('Unexpected Error', 'Could not update hexagon: ' + (error.message || 'unknown error'), 'error');
    } finally {
        button.textContent = "Done!";
        hexagon.polygon._map.closePopup();
    }
}