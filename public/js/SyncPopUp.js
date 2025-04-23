function floatingButtonAction() {
    openPopup();
}

document.addEventListener("DOMContentLoaded", function() {
    // Set the default content to activities
    changeContent('activities');

    const activityCards = document.querySelectorAll('.activities-card');
    const activityDetails = document.getElementById('activity-details');
    const detailsActivityName = document.getElementById('details-activity-name');
    const detailsActivityDetails = document.getElementById('details-activity-details');

    activityCards.forEach(card => {
        card.addEventListener('click', function() {
            detailsActivityName.textContent = this.getAttribute('data-activity-name');
            detailsActivityDetails.innerHTML = '';
            detailsActivityDetails.appendChild(this.cloneNode(true)); // copies map properly
            activityDetails.style.display = 'block';
            document.getElementById('activities-content').style.display = 'none'; //hide the activity list.
            openPopup(); // Open the popup if it's not already open
        });
    });

    //close when click outside pop up
    const overlay = document.getElementById('overlay');
    const popup = document.getElementById('popup');

    overlay.addEventListener('click', function (e) {
        if (!popup.contains(e.target)) {
            closePopup();
        }
    });
});

function openPopup() {
    document.getElementById('popup').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
    document.body.classList.add('no-scroll');

    // Wait for popup content to render
    setTimeout(() => {
        document.querySelectorAll('.activities-map').forEach(div => {
            if (!div._leaflet_map) {
                initializeActivityMap(div); // see ActivityMapPopUp.js
            } else {
                div._leaflet_map.invalidateSize();
                div._leaflet_map.fitBounds(div._leaflet_polyline.getBounds());
            }
        });
    }, 0);
}

function closePopup() {
    document.getElementById('popup').style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
    document.body.classList.remove('no-scroll'); // Restore scrolling
    document.getElementById('activities-content').style.display = 'block'; //show activity list again on close.
    document.getElementById('activity-details').style.display = 'none'; //hide the activity details again on close.
}