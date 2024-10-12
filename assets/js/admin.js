// assets/js/admin.js

const loader = document.querySelector('.loader > img')
const loaderP = document.querySelector('.loader > p')

// Déclaration de la fonction sendPostRequest dans l'espace global
function alertT(){
    alert('coucou')
}
function sendPostRequest(url) {
    loader.classList.remove('not-visible')
    loaderP.classList.remove('not-visible')

    fetch(url, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    }).then(response => {
        if (response.ok) {
            loader.classList.add('not-visible')
            loaderP.classList.add('not-visible')
            location.reload(); // Recharge la page pour voir les messages flash
        } else {
            alert("Une erreur est survenue.");
        }
    }).catch(error => {
        console.error('Erreur:', error);
    });
}

