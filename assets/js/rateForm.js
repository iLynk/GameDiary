// Ce script est utilisé pour traiter de façon dynamique la création d'avis

const formButton = document.querySelector(".show-review-form");
const formDiv = document.querySelector("#review-form");
const form = document.querySelector("#review-form > form");
let formShow = false;
formDiv.style.display = 'none';

// Fonction pour afficher / cacher le formulaire quand on clique sur le bouton
formButton.addEventListener('click', function () {
    formShow = !formShow;
    formDiv.style.display = formShow ? 'block' : 'none';
});

// Envoi du formulaire en FETCH
form.addEventListener('submit', function (event) {
    event.preventDefault();

    let formData = new FormData(form);
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        // Je n'ai pas setup de CSRF token puisqu'il est déjà généré par mon FormType Symfony
        },

    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur serveur : ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Erreur : ' + data.errors);
            }
        })
        .catch(error => {
            console.error('Erreur lors de la soumission du formulaire', error);
            alert('Erreur inattendue lors de la soumission.');
        });
});
