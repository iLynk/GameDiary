// Ce script gère le bouton pour revenir en haut de la page
let scrollToTopBtn = document.getElementById("scrollToTopBtn");
let rootElement = document.documentElement;

function showScroll() {
    const yPosition = 1000;  // Position en pixels où le bouton apparaît

    if (rootElement.scrollTop > yPosition) {
        scrollToTopBtn.style.display = "block";
    } else {
        scrollToTopBtn.style.display = "none";
    }
}

function scrollToTop() {
    rootElement.scrollTo({
        top: 0,
        behavior: "smooth"
    });
}
scrollToTopBtn.addEventListener("click", scrollToTop);
document.addEventListener("scroll", showScroll);
