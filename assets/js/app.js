console.log("cc")
document.addEventListener("DOMContentLoaded", function () {
    const burger = document.querySelector(".burger");
    const mobileMenu = document.querySelector(".mobile-menu");

    burger.addEventListener("click", function () {
        mobileMenu.classList.toggle("hidden");
    });

});
