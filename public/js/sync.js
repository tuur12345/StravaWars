// Add event listeners for the shop items when they're clicked
//document.addEventListener('DOMContentLoaded', function() {
    // Fetch initial balance
    fetchStravabucks();

    // Handle shop price clicks
    document.querySelectorAll('.price').forEach(priceElement => {
        priceElement.addEventListener('click', function() {
            const itemContainer = this.closest('.hexagon-container');
            const itemName = itemContainer.querySelector('img').alt;
            const cost = parseInt(this.querySelector('span').textContent);

            // Attempt to purchase the item
            useStravabucks(cost, itemName);
        });
    });

    // If there's a button to collect coins from kudos
    const collectKudosBtn = document.getElementById('collect-kudos-btn');
    if (collectKudosBtn) {
        console.log('EventListener toegevoegd aan collect-kudos-btn');
        collectKudosBtn.addEventListener('click', function () {
            console.log('Kudos button clicked');
            const kudosToCoins = parseInt(this.dataset.amount || 0);
            console.log('addStravabucks called with:', kudosToCoins);
            if (kudosToCoins > 0) {
                console.log('addStravabucks inside:', amount);

                fetch('/add-stravabucks', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ amount: amount })
                })
                    .then(response => {
                        console.log('Response received');
                        return response.json();
                    })
                    .then(data => {
                        console.log('Response data:', data);

                        if (data.status === 'success') {
                            updateCoinsDisplay(data.current_balance);
                            showNotification('Success', `Added ${amount} Stravabucks!`, 'success');
                        } else {
                            showNotification('Error', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error adding stravabucks:', error);
                        showNotification('Error', 'Failed to add Stravabucks', 'error');
                    })

                this.disabled = true;
                this.textContent = 'Collected!';
            }
        });
    }
//});


// duplicated ??

// function changeContent(contentType) {
//     // Controleer of de configuratie bestaat
//     if (typeof syncPopupConfig === 'undefined' || !syncPopupConfig.assetPaths) {
//         console.error('syncPopupConfig or necessary assetPaths not found!');
//         return; // Stop uitvoering als data mist
//     }
//     const paths = syncPopupConfig.assetPaths; // Haal de paden op
//
//     // Verberg content (zoals voorheen)
//     document.getElementById('activities-content').style.display = 'none';
//     document.getElementById('kudosconverter').style.display = 'none';
//     document.getElementById('activity-details').style.display = 'none';
//
//     // Selecteer de afbeeldingen (veiliger met check of ze bestaan)
//     const img1 = document.querySelector('.sidebar-btn:nth-child(1) img');
//     const img2 = document.querySelector('.sidebar-btn:nth-child(2) img');
//
//     if (contentType === 'activities') {
//         // Gebruik de paden uit het configuratie object
//         if (img1) img1.src = paths.orangeActivity;
//         if (img2) img2.src = paths.whiteThumbsup;
//         document.getElementById('activities-content').style.display = 'block';
//     } else if (contentType === 'kudosconverter') {
//         // Gebruik de paden uit het configuratie object
//         if (img1) img1.src = paths.whiteActivity;
//         if (img2) img2.src = paths.orangeThumbsup;
//         document.getElementById('kudosconverter').style.display = 'block';
//     }
//     // Voeg eventueel logica voor 'activity-details' toe
// }