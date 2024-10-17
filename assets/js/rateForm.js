const formButton = document.querySelector(".show-review-form");
const formDiv = document.querySelector("#review-form");
const form = document.querySelector("#review-form > form");
let formShow = false;
formDiv.style.display = 'none';

// Fonction pour afficher / cacher le formulaire
formButton.addEventListener('click', function () {
    formShow = !formShow;
    formDiv.style.display = formShow ? 'block' : 'none';
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
