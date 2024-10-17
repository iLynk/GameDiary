const stars = document.querySelectorAll('.star');
const ratingInput = document.querySelector('input[name="review[rate]"]');
function updateStarSelection(rating) {
    stars.forEach(s => {
        s.classList.remove('selected');
        if (s.getAttribute('data-value') <= rating) {
            s.classList.add('selected');
        }
    });
}
stars.forEach(star => {
    // Lorsqu'on survole une étoile, on met en surbrillance les étoiles précédentes
    star.addEventListener('mouseover', function () {
        const rating = this.getAttribute('data-value');
        updateStarSelection(rating);
    });

    // Lorsqu'on clique sur une étoile, on enregistre la note
    star.addEventListener('click', function () {
        const rating = this.getAttribute('data-value');
        ratingInput.value = rating;
        updateStarSelection(rating);
    });

    // Lorsque la souris sort des étoiles, on rétablit la note actuelle
    star.addEventListener('mouseout', function () {
        const rating = ratingInput.value || 0;
        updateStarSelection(rating);
    });
});