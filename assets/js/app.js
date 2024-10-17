console.log("cc")
document.addEventListener("DOMContentLoaded", function () {
    const burger = document.querySelector(".burger");
    const mobileMenu = document.querySelector(".mobile-menu");

    burger.addEventListener("click", function () {
        mobileMenu.classList.toggle("hidden");
    });

});

// Back-to-Top functionality
const backToTopButton = document.getElementById('backToTop');

window.onscroll = function () {
    // Affiche le bouton après avoir défilé de 100 pixels
    if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
        backToTopButton.classList.add('show');
    } else {
        backToTopButton.classList.remove('show');
    }
};

// Remonter en haut de la page quand le bouton est cliqué
backToTopButton.addEventListener('click', function () {
    window.scrollTo({ top: 0, behavior: 'smooth' });
});
