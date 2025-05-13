
document.addEventListener("DOMContentLoaded", function () {
    const toggleButton = document.querySelector('#dark-mode a'); // Dark mode toggle button
    const profilePicture = document.getElementById('profile-picture') // Profile picture
    const html = document.documentElement;

    if (localStorage.getItem("darkMode") === "enabled") {
        html.classList.add("dark-mode");
    }

    // Toggle dark mode
    toggleButton.addEventListener('click', function (event) {
        // Save user preference
        if (html.classList.contains("dark-mode")) {
            localStorage.setItem("darkMode", "enabled");
        } else {
            localStorage.setItem("darkMode", "disabled");
        }
    });
});

