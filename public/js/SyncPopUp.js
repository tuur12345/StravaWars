// js/SyncPopUp.js

(function() {
    'use strict'; // Helpt bij het opsporen van veelvoorkomende fouten

    // --- Hulpfunctie om configuratie veilig op te halen ---
    function getSyncConfig() {
        if (typeof globalConfig !== 'undefined' && globalConfig.syncPopup) {
            // Controleer of essentiële onderdelen bestaan
            if (globalConfig.syncPopup.assetPaths && globalConfig.syncPopup.urls && globalConfig.syncPopup.initialKudosState) {
                return globalConfig.syncPopup;
            } else {
                console.error("SyncPopup config is incomplete (missing assetPaths, urls, or initialKudosState).", globalConfig.syncPopup);
                return null;
            }
        }
        console.error("globalConfig.syncPopup not found!");
        return null;
    }

    // --- Kernfuncties ---

    function openPopup() {
        const overlay = document.getElementById('overlay');
        const popup = document.getElementById('popup');
        if (!overlay || !popup) {
            console.error("Overlay or Popup element not found.");
            return;
        }

        console.log("Opening popup...");
        overlay.style.display = 'block';
        popup.style.display = 'block';
        document.body.classList.add('no-scroll');

        // Zet standaard de 'activities' tab actief bij openen
        changeContent('activities');

        // Initialiseer/update kaarten in de popup (vooral die in #activities-content)
        // Wacht even tot de popup CSS volledig is toegepast
        setTimeout(() => {
            const mapsToUpdate = popup.querySelectorAll('#activities-content .activities-map');
            console.log(`Found ${mapsToUpdate.length} maps in #activities-content to initialize/update.`);
            mapsToUpdate.forEach(mapDiv => {
                initializeOrUpdateMap(mapDiv, false); // Kaarten in lijst zijn niet clickable
            });
        }, 50); // Kleine delay
    }

    function closePopup() {
        const overlay = document.getElementById('overlay');
        const popup = document.getElementById('popup');
        if (!overlay || !popup) return;

        console.log("Closing popup...");
        overlay.style.display = 'none';
        popup.style.display = 'none';
        document.body.classList.remove('no-scroll');

        // Optioneel: Reset view naar activities (kan ook weggelaten worden)
        // changeContent('activities');
        // Verberg details view expliciet bij sluiten
        const detailsContent = document.getElementById('activity-details');
        if(detailsContent) detailsContent.style.display = 'none';
    }

    function changeContent(contentType) {
        const config = getSyncConfig();
        if (!config || !config.assetPaths) return; // Stop als config mist

        const paths = config.assetPaths;
        const activitiesContent = document.getElementById('activities-content');
        const kudosContent = document.getElementById('kudosconverter');
        const detailsContent = document.getElementById('activity-details');

        // Robuuste selectie van knop-afbeeldingen
        const img1 = document.querySelector('#popup .sidebar-btn[data-content-type="activities"] img');
        const img2 = document.querySelector('#popup .sidebar-btn[data-content-type="kudosconverter"] img');

        // Verberg alle content secties eerst
        if (activitiesContent) activitiesContent.style.display = 'none';
        if (kudosContent) kudosContent.style.display = 'none';
        if (detailsContent) detailsContent.style.display = 'none';

        console.log(`SyncPopup: Changing content to: ${contentType}`);

        // Toon de juiste sectie en update iconen
        if (contentType === 'activities') {
            if (img1) img1.src = paths.orangeActivity;
            if (img2) img2.src = paths.whiteThumbsup;
            if (activitiesContent) {
                activitiesContent.style.display = 'block';
                // Zorg ervoor dat kaarten hier opnieuw worden gevalideerd als nodig
                // (Maar openPopup doet dit al initieel)
            } else console.error("#activities-content not found");

        } else if (contentType === 'kudosconverter') {
            if (img1) img1.src = paths.whiteActivity;
            if (img2) img2.src = paths.orangeThumbsup;
            if (kudosContent) kudosContent.style.display = 'block';
            else console.error("#kudosconverter not found");

        } else if (contentType === 'activity-details') {
            // Sidebar iconen niet aanpassen als details worden getoond
            if (detailsContent) detailsContent.style.display = 'block';
            else console.error("#activity-details not found");
            // Map initialisatie/update voor details gebeurt in showActivityDetails

        } else {
            console.warn(`SyncPopup: Unknown content type requested: ${contentType}`);
        }
    }

    function showActivityDetails(cardElement) {
        const detailsContainer = document.getElementById('activity-details');
        const nameElement = document.getElementById('details-activity-name');
        const contentElement = document.getElementById('details-activity-details');

        if (!detailsContainer || !nameElement || !contentElement || !cardElement) {
            console.error("Cannot show activity details, required elements or source card missing.");
            return;
        }

        const activityName = cardElement.dataset.activityName || 'Activity Details';
        console.log(`Showing details for: ${activityName}`);

        nameElement.textContent = activityName;
        contentElement.innerHTML = ''; // Leegmaken

        // Kloon de kaart inhoud om in de details te plaatsen
        const cardContentClone = cardElement.cloneNode(true);
        const mapDiv = cardContentClone.querySelector('.activities-map');

        if (mapDiv) {
            // Geef de gekloonde map een unieke ID (optioneel, maar kan helpen)
            const originalId = mapDiv.id;
            mapDiv.id = `details-${originalId}`;
            mapDiv.setAttribute('data-clickable', 'true'); // Maak deze kaart clickable

            // Belangrijk: Verwijder interne Leaflet state van de kloon
            // Dit voorkomt conflicten als Leaflet dezelfde div probeert te beheren
            delete mapDiv._leaflet_id; // Als Leaflet dit gebruikt
            delete mapDiv._leaflet_map; // Verwijder referentie naar oude map instantie
        }

        contentElement.appendChild(cardContentClone);
        changeContent('activity-details'); // Schakel naar de details view

        // Initialiseer/update de kaart in de details view
        const newMapDiv = contentElement.querySelector('.activities-map');
        if (newMapDiv) {
            setTimeout(() => { // Kleine delay
                initializeOrUpdateMap(newMapDiv, true); // Deze map is clickable
            }, 50);
        }
    }

    function initializeOrUpdateMap(mapDiv, isClickable) {
        if (!mapDiv) return;

        if (typeof L === 'undefined' || typeof L.map !== 'function') {
            console.error("Leaflet (L) is not loaded or available.");
            return;
        }
        if (typeof polyline === 'undefined' || typeof polyline.decode !== 'function') {
            console.error("Polyline library is not loaded or available.");
            return;
        }
        if (typeof initializeActivityMap !== 'function') {
            console.error("initializeActivityMap function (from ActivityMap.js) not found.");
            // Eventueel fallback of simpele kaart hier tonen?
            return;
        }


        // Check of er al een map instantie is
        if (mapDiv._leaflet_map instanceof L.Map) {
            console.log(`Map already initialized for ${mapDiv.id}. Invalidating size.`);
            mapDiv._leaflet_map.invalidateSize();
            // Optioneel: opnieuw inzoomen op polyline
            // if (mapDiv._leaflet_polyline) {
            //     mapDiv._leaflet_map.fitBounds(mapDiv._leaflet_polyline.getBounds());
            // }
        } else {
            console.log(`Initializing new map for ${mapDiv.id}. Clickable: ${isClickable}`);
            // Roep de functie uit ActivityMap.js aan
            // Deze functie zou nu de kaart moeten maken, route en hexagons toevoegen
            initializeActivityMap(mapDiv, isClickable);
        }
    }

    function addStravabucks(amount, url, buttonElement) {
        if (!url) {
            console.error('Add Stravabucks URL is missing.');
            if(typeof showNotification === 'function') showNotification('Fout', 'Configuratie fout: Kan server niet contacteren.', 'error');
            else alert("Configuratie fout: Kan server niet contacteren.");
            return;
        }
        if (!buttonElement) {
            console.error("Button element missing for addStravabucks call.");
            return;
        }
        if (amount <= 0) {
            console.log("Amount is zero or negative, not adding Stravabucks.");
            return;
        }

        console.log(`SyncPopup: Attempting to add ${amount} Stravabucks via ${url}`);
        buttonElement.disabled = true; // Disable direct
        buttonElement.textContent = 'Processing...'; // Feedback

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ amount: amount })
        })
            .then(response => {
                if (!response.ok) {
                    return response.json().catch(() => null).then(errData => {
                        throw new Error(errData?.message || `Server error: ${response.status}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log("SyncPopup: Add Stravabucks server response:", data);
                if (data.status === 'success') {
                    // Update de coin display
                    if (typeof updateCoinsDisplay === 'function') {
                        updateCoinsDisplay(data.current_balance);
                    } else {
                        console.warn("Function 'updateCoinsDisplay' not found.");
                    }
                    // Update knop en melding
                    buttonElement.textContent = 'Collected!'; // Blijft disabled
                    const messageContainer = buttonElement.closest('#kudosconverter')?.querySelector('.conversion-message');
                    if (messageContainer) {
                        messageContainer.textContent = data.message || 'Kudos converted successfully!';
                        messageContainer.style.display = 'block';
                    }
                    // Toon succes notificatie
                    if (typeof showNotification === 'function') {
                        showNotification('Success', data.message || 'Kudos converted!', 'success');
                    }
                    // Update sessie status client-side (indien nodig, maar reload is betrouwbaarder)
                    // Voor nu vertrouwen we op de server sessie en de disabled state
                } else {
                    // Fout terug van server (bv. al geconverteerd, validatie etc.)
                    buttonElement.textContent = 'Failed'; // Update tekst
                    // Her-activeer knop NIET als de fout bv. 'al geconverteerd' is.
                    // Alleen her-activeren bij bv. tijdelijke serverfout? Voor nu laten we het disabled.
                    // buttonElement.disabled = false;
                    const messageContainer = buttonElement.closest('#kudosconverter')?.querySelector('.conversion-message');
                    if (messageContainer) {
                        messageContainer.textContent = data.message || 'Conversion failed.';
                        messageContainer.style.display = 'block';
                    }
                    if (typeof showNotification === 'function') {
                        showNotification('Error', data.message || 'Conversion failed.', 'error');
                    } else {
                        alert('Error: ' + (data.message || 'Conversion failed.'));
                    }
                }
            })
            .catch(error => {
                console.error('SyncPopup: Error adding Stravabucks:', error);
                buttonElement.disabled = false; // Her-activeer bij netwerkfout
                buttonElement.textContent = 'Retry?'; // Of terug naar originele staat
                const messageContainer = buttonElement.closest('#kudosconverter')?.querySelector('.conversion-message');
                if(messageContainer) {
                    messageContainer.textContent = 'Error during conversion.';
                    messageContainer.style.display = 'block';
                }
                if (typeof showNotification === 'function') {
                    showNotification('Error', error.message || 'Network error during conversion.', 'error');
                } else {
                    alert('Error: ' + (error.message || 'Network error during conversion.'));
                }
            });
    }


    // --- DOMContentLoaded Event Listener ---
    document.addEventListener("DOMContentLoaded", function() {
        const config = getSyncConfig();
        if (!config) {
            console.error("Sync Popup cannot initialize due to missing configuration.");
            // Optioneel: disable de open knop of toon een foutmelding
            const openBtn = document.getElementById('open-sync-popup-btn');
            if(openBtn) openBtn.disabled = true;
            return;
        }

        // --- Element Selectors ---
        const openBtn = document.getElementById('open-sync-popup-btn');
        const closeBtn = document.getElementById('close-sync-popup-btn');
        const overlay = document.getElementById('overlay');
        const popup = document.getElementById('popup'); // Hoofd popup div
        const sidebarBtns = popup?.querySelectorAll('.sidebar-btn');
        const collectKudosBtn = document.getElementById('collect-kudos-btn');
        // Selecteer de container van de activity cards dynamisch
        const activitiesContent = document.getElementById('activities-content');


        // --- Event Listeners ---
        if (openBtn) {
            openBtn.addEventListener('click', openPopup);
        } else { console.warn("Open button (#open-sync-popup-btn) not found."); }

        if (closeBtn) {
            closeBtn.addEventListener('click', closePopup);
        } else { console.warn("Close button (#close-sync-popup-btn) not found."); }

        if (overlay) {
            overlay.addEventListener('click', function (e) {
                // Sluit alleen als er direct op de overlay geklikt wordt
                if (popup && !popup.contains(e.target) && e.target === overlay) {
                    closePopup();
                }
            });
        } else { console.warn("Overlay element not found."); }

        if (sidebarBtns && sidebarBtns.length > 0) {
            sidebarBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const contentType = this.dataset.contentType;
                    if (contentType) {
                        changeContent(contentType);
                    } else {
                        console.warn("Sidebar button clicked without data-content-type.", this);
                    }
                });
            });
        } else { console.warn("Sidebar buttons (.sidebar-btn) not found inside #popup."); }

        // Event listeners voor activity cards toevoegen (gebruik event delegation voor performance)
        if (activitiesContent) {
            activitiesContent.addEventListener('click', function(event) {
                // Zoek de dichtstbijzijnde .activities-card ouder van het geklikte element
                const card = event.target.closest('.activities-card');
                if (card) {
                    console.log("Activity card clicked via delegation:", card.dataset.activityName);
                    showActivityDetails(card); // Roep de functie aan met de gevonden kaart
                }
            });
        } else { console.warn("Activities content container (#activities-content) not found for delegation."); }


        // Kudos knop listener en initiële staat
        if (collectKudosBtn) {
            const initialState = config.initialKudosState || {};
            const conversionMessage = document.querySelector('#kudosconverter .conversion-message'); // Zoek relatief

            // Stel initiële staat in
            let isDisabled = false;
            let initialText = ''; // Houd rekening met afbeelding
            let initialMessage = '';

            if (initialState.kudosAlreadyConverted && initialState.kudostocoins > 0) {
                isDisabled = true;
                initialText = 'Collected!';
                if (conversionMessage) initialMessage = 'Kudos already converted!';
            } else if (initialState.kudostocoins <= 0) {
                isDisabled = true;
                initialText = 'No Kudos';
                if (conversionMessage && !initialState.kudosAlreadyConverted) initialMessage = 'No kudos to convert.';
                else if (conversionMessage) initialMessage = ''; // Geen melding als al geconverteerd EN 0 kudos
            }

            collectKudosBtn.disabled = isDisabled;
            // Als de knop een afbeelding bevat, vervang de hele inhoud. Anders alleen tekst.
            const btnImg = collectKudosBtn.querySelector('img');
            if (isDisabled && btnImg) { // Als disabled en er is een img, vervang door tekst
                collectKudosBtn.innerHTML = initialText;
            } else if (isDisabled) { // Als disabled en geen img, zet tekst
                collectKudosBtn.textContent = initialText;
            } else if(btnImg && config.assetPaths.convertButtonIcon) { // Als enabled en img bestaat, zet juiste src
                btnImg.src = config.assetPaths.convertButtonIcon;
                btnImg.alt = "Convert Kudos";
            } // Anders (enabled, geen img) blijft de tekst zoals hij is

            if (conversionMessage && initialMessage) {
                conversionMessage.textContent = initialMessage;
                conversionMessage.style.display = 'block';
            } else if (conversionMessage) {
                conversionMessage.style.display = 'none'; // Verberg lege melding
            }


            // Voeg listener alleen toe als NIET initieel disabled
            if (!isDisabled) {
                collectKudosBtn.addEventListener('click', function(event) {
                    event.preventDefault();
                    const amount = parseInt(this.dataset.amount || '0');
                    if (config.urls && config.urls.addStravabucks) {
                        addStravabucks(amount, config.urls.addStravabucks, this);
                    } else {
                        console.error("Add Stravabucks URL not configured.");
                        if(typeof showNotification === 'function') showNotification('Fout', 'Configuratie fout.', 'error');
                    }
                });
            }
        } else { console.warn("Collect kudos button (#collect-kudos-btn) not found."); }

        // Zet de initiële view (optioneel, openPopup doet dit ook)
        // changeContent('activities');

        console.log("SyncPopup Initialized");

    }); // Einde DOMContentLoaded

})(); // Einde IIFE