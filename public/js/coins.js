
    // --- Scope Variables ---
    let selectedItem = {
        name: '',
        price: 0,
        imageUrl: '',
        element: null,
        itemIdentifier: '' // bv. 'trap', 'fake', 'poison'
    };
    let currentInventory = { trap: 0, fake: 0, poison: 0 }; // Default structure

    // --- Utility Functions ---

    // Function to get config safely
    function getConfig(section = null) {
        if (typeof globalConfig === 'undefined') {
            console.error("globalConfig is not defined!");
            return null;
        }
        if (section && typeof globalConfig[section] === 'undefined') {
            console.error(`globalConfig.${section} is not defined!`);
            return null;
        }
        return section ? globalConfig[section] : globalConfig;
    }

    // --- Core Shop Functions ---

    function confirmPurchase() {
        const shopConfig = getConfig('shop');
        if (!shopConfig || !shopConfig.urls) {
            showNotification('Fout', 'Winkel configuratie niet gevonden.', 'error');
            return;
        }

        const quantityInput = document.getElementById('itemQuantity');
        if (!quantityInput) {
            console.error("Quantity input not found");
            return;
        }
        const quantity = parseInt(quantityInput.value);
        const totalCost = selectedItem.price * quantity;
        const itemNameForDisplay = selectedItem.name;
        const itemIdentifierForDB = selectedItem.itemIdentifier;

        if (!itemIdentifierForDB) {
            showNotification('Fout', 'Item identifier niet gevonden. Kan aankoop niet voltooien.', 'error');
            console.error("itemIdentifier is leeg in confirmPurchase. selectedItem:", selectedItem);
            return;
        }
        if (quantity <= 0) {
            showNotification('Info', 'Selecteer een geldig aantal.', 'info');
            return;
        }

        console.log(`Attempting purchase: ${quantity}x ${itemNameForDisplay} (${itemIdentifierForDB}) for ${totalCost} Stravabucks`);

        // STAP 1: Use Stravabucks
        fetch(shopConfig.urls.useStravabucks, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ amount: totalCost, itemName: `${quantity}x ${itemNameForDisplay}` }) // Send item name for logging/context
        })
            .then(response => response.json())
            .then(stravabucksData => {
                console.log("Use Stravabucks response:", stravabucksData);
                if (stravabucksData.status === 'success') {
                    // Update display immediately (optional, page reload will handle it too)
                    if (typeof updateCoinsDisplay === 'function') {
                        updateCoinsDisplay(stravabucksData.current_balance);
                    }

                    // STAP 2: Add to Inventory
                    fetch(shopConfig.urls.addToInventory, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ itemName: itemIdentifierForDB, quantity: quantity })
                    })
                        .then(response => response.json())
                        .then(inventoryData => {
                            console.log("Add to Inventory response:", inventoryData);
                            if (inventoryData.status === 'success') {
                                // ALL GOOD! Set notification and reload.
                                localStorage.setItem('stravabucks_notification', JSON.stringify({
                                    title: 'Aankoop Succesvol!',
                                    message: `${quantity}x ${itemNameForDisplay} gekocht voor ${totalCost} Stravabucks en toegevoegd. Nieuw saldo: ${stravabucksData.current_balance}.`,
                                    type: 'success'
                                }));
                                location.reload();
                            } else {
                                // Stravabucks deducted, inventory failed. Log error, notify user, reload.
                                console.error(`Inventory add failed after purchase: ${inventoryData.message}. Item: ${itemIdentifierForDB}, Qty: ${quantity}`);
                                localStorage.setItem('stravabucks_notification', JSON.stringify({
                                    title: 'Deels Mislukte Aankoop',
                                    message: `Je Stravabucks (${totalCost}) zijn afgeschreven, maar ${itemNameForDisplay} kon niet aan de inventaris worden toegevoegd: ${inventoryData.message}. Nieuw saldo: ${stravabucksData.current_balance}.`,
                                    type: 'error'
                                }));
                                location.reload();
                            }
                        })
                        .catch(error => {
                            // Network or server error during inventory add. Log error, notify user, reload.
                            console.error('Error adding to inventory:', error);
                            localStorage.setItem('stravabucks_notification', JSON.stringify({
                                title: 'Fout bij Inventaris',
                                message: `Er ging iets mis bij het toevoegen aan de inventaris na je aankoop (${error.message || 'onbekende fout'}). Controleer je saldo en inventaris. Saldo zou nu ${stravabucksData.current_balance} moeten zijn.`,
                                type: 'error'
                            }));
                            location.reload();
                        });
                } else {
                    // Failed to use Stravabucks (e.g., insufficient funds)
                    showNotification('Aankoop Mislukt', stravabucksData.message || 'Kon Stravabucks niet afschrijven.', 'error');
                    // Keep confirmation popup open
                    closePurchasePopup(); // Sluit enkel de confirmation popup
                }
            })
            .catch(error => {
                // Network or server error during use stravabucks
                console.error('Error using stravabucks:', error);
                showNotification('Fout', `Kon aankoop niet voltooien (${error.message || 'onbekende fout'}). Controleer je verbinding.`, 'error');
                closePurchasePopup(); // Sluit confirmation popup
            });
    }

    function fetchInventory() {
        const shopConfig = getConfig('shop');
        if (!shopConfig || !shopConfig.urls || !shopConfig.urls.getInventory) {
            console.error("Get Inventory URL not configured.");
            renderInventory(); // Render with default empty inventory
            return;
        }

        fetch(shopConfig.urls.getInventory)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success' && data.inventory) {
                    currentInventory = data.inventory;
                    console.log("Inventory fetched:", currentInventory);
                } else {
                    console.warn('Failed to fetch inventory or inventory empty:', data.message);
                    currentInventory = { trap: 0, fake: 0, poison: 0 }; // Reset to default
                }
                // Only render if the inventory tab is currently active
                if (document.querySelector('.menu-button[data-tab="inventory"].active')) {
                    renderInventory();
                }
            })
            .catch(error => {
                console.error('Error fetching inventory:', error);
                currentInventory = { trap: 0, fake: 0, poison: 0 }; // Reset to default
                // Only render if the inventory tab is currently active
                if (document.querySelector('.menu-button[data-tab="inventory"].active')) {
                    renderInventory(); // Render empty state on error
                }
            });
    }

    function renderInventory() {
        const shopConfig = getConfig('shop');
        const shopContent = document.getElementById('shopContent');
        if (!shopContent) return; // Exit if element not found

        // Check if inventory tab is active - optional, could always render if needed
        const inventoryTabActive = document.querySelector('.menu-button[data-tab="inventory"].active');
        if (!inventoryTabActive) {
            // console.log("Inventory tab not active, not rendering.");
            // return; // Uncomment if you ONLY want to render when tab is active
        }

        let inventoryHTML = '<h2>Inventory:</h2>';
        // Check if there are any items with count > 0
        const hasItems = Object.values(currentInventory).some(count => count > 0);

        if (!hasItems) {
            inventoryHTML += '<div class="empty-inventory"><p>You have no items in your inventory yet.</p></div>';
        } else {
            inventoryHTML += '<div class="inventory-grid">';
            if (!shopConfig || !shopConfig.assetPaths) {
                console.error("Shop asset paths not configured for inventory rendering.");
                inventoryHTML += '<p>Error loading item images.</p>';
            } else {
                const paths = shopConfig.assetPaths;
                // Define mappings (adjust keys if needed)
                const itemImagePaths = {
                    trap: paths.trap,
                    fake: paths.fake, // Assuming 'fake' is the key in currentInventory
                    poison: paths.poison
                };
                const itemDisplayNames = { trap: "Traps", fake: "Fake Hexagons", poison: "Poisons" };

                for (const itemType in currentInventory) {
                    if (currentInventory[itemType] > 0) {
                        const imagePath = itemImagePaths[itemType] || paths.defaultItemIcon || ''; // Use fallback
                        const displayName = itemDisplayNames[itemType] || itemType.charAt(0).toUpperCase() + itemType.slice(1);
                        inventoryHTML += `
                        <div class="inventory-item">
                            <img src="${imagePath}" alt="${displayName}">
                            <p>${displayName}: ${currentInventory[itemType]}</p>
                        </div>`;
                    }
                }
            }
            inventoryHTML += '</div>'; // Close inventory-grid
        }
        shopContent.innerHTML = inventoryHTML;
        shopContent.style.display = 'block'; // Make sure content area is visible
    }

    function renderShopContent(shopContentElement) {
        const shopConfig = getConfig('shop');
        if (!shopContentElement || !shopConfig || !shopConfig.assetPaths || !shopConfig.urls) {
            console.error("Cannot render shop content: Element or config missing.");
            if(shopContentElement) shopContentElement.innerHTML = '<p>Error loading shop items.</p>';
            return;
        }
        const paths = shopConfig.assetPaths;

        // Dynamically build shop HTML using config
        // Note: data-item should match keys in inventory/config (e.g., 'trap', 'fake')
        shopContentElement.innerHTML = `
            <div class="hexagon-container">
                <img src="${paths.trap || ''}" alt="Trap">
                <div class="price" data-item="trap" data-price="15">
                    <img src="${paths.stravabucksIcon || ''}" alt="Coin Icon"> <span>15</span>
                </div>
            </div>
            <div class="hexagon-container">
                <img src="${paths.fake || ''}" alt="Fake Hexagon">
                <div class="price" data-item="fake" data-price="15">
                    <img src="${paths.stravabucksIcon || ''}" alt="Coin Icon"> <span>15</span>
                </div>
            </div>
            <div class="hexagon-container">
                <img src="${paths.poison || ''}" alt="Poison">
                <div class="price" data-item="poison" data-price="15">
                    <img src="${paths.stravabucksIcon || ''}" alt="Coin Icon"> <span>15</span>
                </div>
            </div>
            `;
        // Re-attach listeners to the newly created elements
        attachPriceClickListeners(shopContentElement);
        shopContentElement.style.display = 'flex'; // Assuming flex display for shop items
    }

    function attachPriceClickListeners(parentElement) {
        parentElement.querySelectorAll('.price').forEach(priceElement => {
            // Remove existing listener before adding new one to prevent duplicates if re-rendered
            priceElement.removeEventListener('click', handlePriceClick); // Use named function
            priceElement.addEventListener('click', handlePriceClick); // Add named function
        });
    }

    function handlePriceClick() { // Named function for easy removal/addition
        toggleActive(this);
    }

    // --- Popup Handling ---

    function CoinPopup() {
        const overlay = document.getElementById('coinsoverlay');
        const popup = document.getElementById('coinspopup');
        if (!overlay || !popup) return;

        overlay.style.display = 'block';
        popup.style.display = 'block'; // Zorg dat de popup zichtbaar is

        // Stel een default actieve tab in of refresh de huidige
        let activeTabButton = popup.querySelector('.shop-menu .menu-button.active');
        if (!activeTabButton) {
            activeTabButton = popup.querySelector('.shop-menu .menu-button[data-tab="shop"]');
        }
        if (activeTabButton) {
            // Roep de tab switch functie aan om de content te laden/tonen
            switchTab(activeTabButton);
        } else {
            console.warn("No active or default tab button found.");
        }
    }

    function CoinclosePopup() {
        const overlay = document.getElementById('coinsoverlay');
        if (overlay) {
            overlay.style.display = 'none';
        }
        // Optioneel: reset selectie wanneer popup sluit
        selectedItem = { name: '', price: 0, imageUrl: '', element: null, itemIdentifier: '' };
    }

    function openPurchasePopup(element) {
        const shopConfig = getConfig('shop');
        const purchaseOverlay = document.getElementById('purchaseConfirmOverlay');
        const nameElement = document.getElementById('previewItemName');
        const imageElement = document.getElementById('previewItemImage');
        const quantityInput = document.getElementById('itemQuantity');

        if (!purchaseOverlay || !nameElement || !imageElement || !quantityInput || !shopConfig || !shopConfig.assetPaths) {
            console.error("Required elements or config for purchase popup not found.");
            return;
        }

        // Store selected item details
        selectedItem.element = element; // The .price element
        const itemContainer = element.closest('.hexagon-container');
        if (!itemContainer) {
            console.error("Could not find parent .hexagon-container for", element);
            return;
        }
        const itemImg = itemContainer.querySelector('img');

        selectedItem.name = itemImg ? itemImg.alt : 'Unknown Item';
        selectedItem.price = parseInt(element.dataset.price || '0'); // Default to 0 if missing
        selectedItem.imageUrl = itemImg ? itemImg.src : (shopConfig.assetPaths.defaultItemIcon || ''); // Use fallback from config
        selectedItem.itemIdentifier = element.dataset.item || ''; // Get identifier

        if (!selectedItem.itemIdentifier) {
            console.error("Missing 'data-item' attribute on price element:", element);
            showNotification('Fout', 'Kan item details niet laden (missende identifier).', 'error');
            return;
        }
        if (selectedItem.price <= 0) {
            console.error("Item price is invalid:", selectedItem.price);
            showNotification('Fout', 'Kan item details niet laden (ongeldige prijs).', 'error');
            return;
        }

        // Update popup content
        nameElement.textContent = selectedItem.name;
        imageElement.src = selectedItem.imageUrl;
        quantityInput.value = 1; // Reset quantity
        updateTotalCost(); // Update cost display

        // Show popup
        purchaseOverlay.style.display = 'flex'; // Use flex as per CSS potentially
    }

    function closePurchasePopup() {
        const purchaseOverlay = document.getElementById('purchaseConfirmOverlay');
        if(purchaseOverlay) {
            purchaseOverlay.style.display = 'none';
        }
        // Deselect item visually (optional)
        if (selectedItem.element) {
            selectedItem.element.classList.remove('active');
        }
        // Reset selectie
        selectedItem = { name: '', price: 0, imageUrl: '', element: null, itemIdentifier: '' };
    }

    // --- Quantity and Cost ---

    function increaseQuantity() {
        const quantityInput = document.getElementById('itemQuantity');
        if (!quantityInput) return;
        let currentValue = parseInt(quantityInput.value);
        const maxQuantity = parseInt(quantityInput.max || '10'); // Use max attribute or default
        if (currentValue < maxQuantity) {
            quantityInput.value = ++currentValue;
            updateTotalCost();
        }
    }

    function decreaseQuantity() {
        const quantityInput = document.getElementById('itemQuantity');
        if (!quantityInput) return;
        let currentValue = parseInt(quantityInput.value);
        const minQuantity = parseInt(quantityInput.min || '1'); // Use min attribute or default
        if (currentValue > minQuantity) {
            quantityInput.value = --currentValue;
            updateTotalCost();
        }
    }

    function updateTotalCost() {
        const shopConfig = getConfig('shop');
        const quantityInput = document.getElementById('itemQuantity');
        const totalCostElement = document.getElementById('totalCost');
        const stravabucksIconElement = totalCostElement?.nextElementSibling; // Assuming icon is next sibling

        if (!quantityInput || !totalCostElement || !shopConfig || !shopConfig.assetPaths) return;

        const quantity = parseInt(quantityInput.value);
        const cost = selectedItem.price * quantity;
        totalCostElement.textContent = cost; // Update only the number part

        // Ensure Stravabucks icon is present and correct
        if (stravabucksIconElement && stravabucksIconElement.tagName === 'IMG') {
            stravabucksIconElement.src = shopConfig.assetPaths.stravabucksIcon || '';
        } else if (totalCostElement) {
            // If icon is missing, add it (basic example)
            const img = document.createElement('img');
            img.src = shopConfig.assetPaths.stravabucksIcon || '';
            img.alt = "Coin Icon";
            img.style.width = "20px";
            img.style.height = "auto";
            img.style.verticalAlign = "middle";
            img.style.marginLeft = "5px"; // Add some space
            totalCostElement.parentNode.insertBefore(img, totalCostElement.nextSibling);
        }
    }

    // --- Item Selection ---

    function toggleActive(element) {
        // Deselect all other price elements first
        const shopContent = document.getElementById('shopContent');
        if (shopContent) {
            shopContent.querySelectorAll('.price').forEach(item => {
                if (item !== element) { // Don't remove from the clicked one yet
                    item.classList.remove('active');
                }
            });
        }
        // Toggle active class on the clicked element
        element.classList.toggle('active');

        // If element is now active, open popup, otherwise close if it was for this item
        if (element.classList.contains('active')) {
            openPurchasePopup(element);
        } else {
            // If the currently selected item matches the element we just deactivated, close the popup
            if (selectedItem.element === element) {
                closePurchasePopup();
            }
        }
    }

    // --- Tab Switching ---
    function switchTab(buttonElement) {
        const menuButtons = document.querySelectorAll('.shop-menu .menu-button');
        const shopContent = document.getElementById('shopContent');
        const buyCoinsSection = document.getElementById('buyCoinsSection');

        if (!shopContent || !buyCoinsSection || !buttonElement) return;

        menuButtons.forEach(btn => btn.classList.remove('active'));
        buttonElement.classList.add('active');
        const tab = buttonElement.dataset.tab;

        shopContent.innerHTML = ''; // Clear previous content
        shopContent.style.display = 'none'; // Hide both initially
        buyCoinsSection.style.display = 'none';

        if (tab === 'shop') {
            renderShopContent(shopContent); // Use the function to render
            shopContent.style.display = 'flex'; // Set display after rendering
        } else if (tab === 'inventory') {
            fetchInventory(); // Fetches data AND calls renderInventory if tab is active
            shopContent.style.display = 'block'; // Display will be grid via CSS potentially
        } else if (tab === 'buycoins') {
            buyCoinsSection.style.display = 'block';
        }
    }


    // --- DOMContentLoaded ---
    //document.addEventListener('DOMContentLoaded', function() {
        // Check if required global config exists
        const shopConfig = getConfig('shop');
        if (!shopConfig) {
            console.error("Shop configuration (globalConfig.shop) missing. Shop functionality disabled.");
            // Optionally display an error message to the user in the UI
            const shopWindow = document.getElementById('coinspopup');
            if(shopWindow) shopWindow.innerHTML = '<p style="color: red; padding: 20px;">Error: Shop configuration is missing. Please contact support.</p>';
            //return; // Stop initialization idk what to put here since DOM is gone
        }

        // Get DOM elements
        const coinButton = document.querySelector('.floating-button-coin');
        const overlay = document.getElementById('coinsoverlay');
        const popup = document.getElementById('coinspopup');
        const shopMenu = popup?.querySelector('.shop-menu');
        const menuButtons = shopMenu?.querySelectorAll('.menu-button');
        const mainCloseButton = popup?.querySelector('.close-button'); // Main popup close
        const purchaseOverlay = document.getElementById('purchaseConfirmOverlay');
        const purchasePopup = document.getElementById('purchaseConfirmPopup');
        const purchaseCloseButton = purchasePopup?.querySelector('.close-button');
        const quantityDecreaseButton = purchasePopup?.querySelector('.quantity-controls button:first-of-type');
        const quantityIncreaseButton = purchasePopup?.querySelector('.quantity-controls button:last-of-type');
        const confirmPurchaseButton = purchasePopup?.querySelector('.confirm-button');
        const cancelPurchaseButton = purchasePopup?.querySelector('.cancel-button');


        // --- Attach Event Listeners ---

        if (coinButton) {
            coinButton.addEventListener('click', CoinPopup);
        } else { console.warn("Floating coin button not found."); }

        if (overlay) {
            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) { // Only close if overlay itself is clicked
                    CoinclosePopup();
                }
            });
        } else { console.warn("Coins overlay not found."); }

        if (mainCloseButton) {
            mainCloseButton.addEventListener('click', CoinclosePopup);
        } else { console.warn("Main close button for shop popup not found."); }

        if (menuButtons) {
            menuButtons.forEach(button => {
                // Ensure it's not the close button before adding tab switch logic
                if (!button.classList.contains('close-button')) {
                    button.addEventListener('click', function() {
                        switchTab(this); // Use the new tab switching function
                    });
                }
            });
        } else { console.warn("Shop menu buttons not found."); }

        // Purchase confirmation popup listeners
        if (purchaseCloseButton) {
            purchaseCloseButton.addEventListener('click', closePurchasePopup);
        } else { console.warn("Purchase confirmation close button not found."); }

        if (cancelPurchaseButton) {
            cancelPurchaseButton.addEventListener('click', closePurchasePopup);
        } else { console.warn("Purchase confirmation cancel button not found."); }

        if (confirmPurchaseButton) {
            confirmPurchaseButton.addEventListener('click', confirmPurchase);
        } else { console.warn("Purchase confirmation confirm button not found."); }

        if (quantityDecreaseButton) {
            quantityDecreaseButton.addEventListener('click', decreaseQuantity);
        } else { console.warn("Quantity decrease button not found."); }

        if (quantityIncreaseButton) {
            quantityIncreaseButton.addEventListener('click', increaseQuantity);
        } else { console.warn("Quantity increase button not found."); }


        // --- Initial Setup ---

        // Call fetchStravabucks if it exists globally
        if (typeof fetchStravabucks === 'function') {
            fetchStravabucks();
        } else {
            console.warn("Global function 'fetchStravabucks' not found.");
            // Potentially update coin display with initial value from Twig if available
            // const initialBucksElement = document.querySelector('.floating-button-coin .circle-container');
            // if (initialBucksElement && typeof updateCoinsDisplay === 'function') {
            //    updateCoinsDisplay(initialBucksElement.textContent);
            //}
        }

        // Fetch initial inventory data (needed for badge counts, etc., even if not visible)
        fetchInventory();

        // Set the initial active tab (e.g., 'shop') and render its content
        const initialTabButton = popup?.querySelector('.menu-button[data-tab="shop"]');
        if (initialTabButton) {
            switchTab(initialTabButton); // Use the switchTab function
        } else {
            console.warn("Initial 'shop' tab button not found. Shop may not render correctly.");
            // Fallback: Manually ensure shop content area exists and render into it
            const shopContent = document.getElementById('shopContent');
            if (shopContent) {
                renderShopContent(shopContent);
                shopContent.style.display = 'flex';
                document.getElementById('buyCoinsSection').style.display = 'none';
            }
        }

        // Check for and display pending notifications from localStorage
        const pendingNotification = localStorage.getItem('stravabucks_notification');
        if (pendingNotification) {
            try {
                const notificationData = JSON.parse(pendingNotification);
                if (typeof showNotification === 'function') {
                    showNotification(notificationData.title, notificationData.message, notificationData.type);
                } else {
                    alert(`${notificationData.title}\n${notificationData.message}`); // Fallback alert
                }
                localStorage.removeItem('stravabucks_notification'); // Clear after showing
            } catch (e) {
                console.error("Error parsing notification from localStorage:", e);
                localStorage.removeItem('stravabucks_notification'); // Clear invalid data
            }
        }


        // NOTE: Listener for #collect-kudos-btn removed - assuming it belongs elsewhere (e.g., SyncPopUp.js)

    //}); // End DOMContentLoaded

