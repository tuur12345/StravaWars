// Update the coins display in UI
function updateCoinsDisplay(amount) {
    const coinsCounter = document.querySelector('.circle-container');
    if (coinsCounter) {
        coinsCounter.textContent = amount;

        if (amount >= 100) {
            coinsCounter.classList.remove('single-digit');
            coinsCounter.classList.add('triple-digit');
        } else if (amount >= 10) {
            coinsCounter.classList.remove('single-digit');
            coinsCounter.classList.remove('triple-digit');
            coinsCounter.classList.add('double-digit');
        } else {
            coinsCounter.classList.add('single-digit');
            coinsCounter.classList.remove('double-digit');
            coinsCounter.classList.remove('triple-digit');
        }
    }
}

// Fetch current stravabucks balance
function fetchStravabucks() {
    fetch('/get-stravabucks')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                updateCoinsDisplay(data.stravabucks);
            }
        })
        .catch(error => console.error('Error fetching stravabucks:', error));
}

// Add stravabucks to user's account
function addStravabucks(amount) {
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
                // Save notification to localStorage before reload
                localStorage.setItem('stravabucks_notification', JSON.stringify({
                    title: 'Success',
                    message: `Added ${amount} Stravabucks!`,
                    type: 'success'
                }));

                location.reload();
            } else {
                showNotification('Error', data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error adding stravabucks:', error);
            showNotification('Error', 'Failed to add Stravabucks', 'error');
        });
}

// Use stravabucks (e.g., when buying an item)
function useStravabucks(amount, itemName) {
    fetch('/use-stravabucks', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ amount: amount, itemName: itemName })
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Save notification to localStorage before reload
                localStorage.setItem('stravabucks_notification', JSON.stringify({
                    title: 'Purchase Successful',
                    message: `You bought ${itemName} for ${amount} Stravabucks!`,
                    type: 'success'
                }));

                location.reload();
            } else {
                showNotification('Purchase Failed', data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error using stravabucks:', error);
            showNotification('Error', 'Failed to complete purchase', 'error');
        });
}

// Show notification popup
function showNotification(title, message, type) {
    let notificationContainer = document.getElementById('notification-container');

    if (!notificationContainer) {
        notificationContainer = document.createElement('div');
        notificationContainer.id = 'notification-container';
        notificationContainer.style.position = 'fixed';
        notificationContainer.style.top = '200px';
        notificationContainer.style.right = '20px';
        notificationContainer.style.zIndex = '9999';
        document.body.appendChild(notificationContainer);
    }

    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.style.backgroundColor = type === 'success' ? '#4CAF50' : '#f44336';
    notification.style.color = 'white';
    notification.style.padding = '15px';
    notification.style.marginBottom = '10px';
    notification.style.borderRadius = '5px';
    notification.style.boxShadow = '0 2px 4px rgba(0,0,0,0.2)';
    notification.style.minWidth = '250px';

    const titleElement = document.createElement('div');
    titleElement.style.fontWeight = 'bold';
    titleElement.style.marginBottom = '5px';
    titleElement.textContent = title;

    const messageElement = document.createElement('div');
    messageElement.textContent = message;

    const closeButton = document.createElement('span');
    closeButton.innerHTML = '&times;';
    closeButton.style.float = 'right';
    closeButton.style.cursor = 'pointer';
    closeButton.style.fontWeight = 'bold';
    closeButton.onclick = function () {
        notification.remove();
    };

    notification.appendChild(closeButton);
    notification.appendChild(titleElement);
    notification.appendChild(messageElement);

    notificationContainer.appendChild(notification);

    // Auto remove after 2 seconds
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

// Show pending notification (if present in localStorage) after reload
//document.addEventListener('DOMContentLoaded', () => {
    const notif = localStorage.getItem('stravabucks_notification');
    if (notif) {
        const { title, message, type } = JSON.parse(notif);
        showNotification(title, message, type);
        localStorage.removeItem('stravabucks_notification');
    }

    // Optional: fetch balance on page load
    fetchStravabucks();
//});
