const formButton = document.querySelector(".show-review-form");
const formDiv = document.querySelector("#review-form");
const form = document.querySelector("#review-form > form");
const stars = document.querySelectorAll('.star');
const ratingInput = document.querySelector('input[name="review[rate]"]');
let formShow = false;
formDiv.style.display = 'none';

// Fonction pour afficher / cacher le formulaire
formButton.addEventListener('click', function () {
    formShow = !formShow;
    formDiv.style.display = formShow ? 'block' : 'none';
});

// Fonction pour mettre à jour la sélection visuelle des étoiles
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

// Envoi du formulaire en FETCH pour
form.addEventListener('submit', function (event) {
    event.preventDefault();

    let formData = new FormData(form);
    for (let pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',  // Indiquer qu'il s'agit d'une requête AJAX
        },
    })
        .then(response => {
            if (!response.ok) {
                // Si le statut n'est pas dans la gamme 200-299, on a une erreur
                throw new Error('Erreur serveur : ' + response.statusText);
            }
            return response.json(); // Essaie de traiter la réponse comme du JSON
        })
        .then(data => {
            if (data.success) {

                alert(data.message);
            } else {
                alert('Erreur : ' + data.errors);
            }
        })
        .catch(error => {
            console.error('Erreur lors de la soumission du formulaire', error);
            alert('Erreur inattendue lors de la soumission.');
        });
});
