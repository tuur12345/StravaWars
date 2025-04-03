document.addEventListener("DOMContentLoaded", function () {
    $(function () {
        function slideMenu() {
            var activeState = $("#menu-container .menu-list").hasClass("active");
            $("#menu-container .menu-list").animate({left: activeState ? "-100%" : "0%"}, 400);
        }

        $("#profile-clickable").click(function (event) {
            event.stopPropagation();
            $("#hamburger-menu").toggleClass("open");
            $("#menu-container .menu-list").toggleClass("active");
            slideMenu();
            $("body").toggleClass("overflow-hidden");
        });

        // Prevent clicks inside the menu from closing it
        $("#menu-container").click(function(event) {
            event.stopPropagation();
        });

        $(".menu-list").find(".accordion-toggle").click(function () {
            $(this).next().toggleClass("open").slideToggle("fast");
            $(this).toggleClass("active-tab").find(".menu-link").toggleClass("active");

            $(".menu-list .accordion-content").not($(this).next()).slideUp("fast").removeClass("open");
            $(".menu-list .accordion-toggle").not($(this)).removeClass("active-tab").find(".menu-link").removeClass("active");
        });
    });
});