// assets/js/admin.js

const loader = document.querySelector('.loader > img')
const loaderP = document.querySelector('.loader > p')
const successMessage = document.querySelector('.success-message')

console.log('script correctement chargé');

function sendPostRequest(url) {
    const csrfToken = document.getElementById('csrf-token').value;

    // on affiche le loader et son message
    loader.classList.remove('not-visible');
    loaderP.classList.remove('not-visible');

    fetch(url, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': csrfToken // Ajout du token CSRF
        }
    }).then(response => {
        if (response.ok) {
            // on masque le loader ainsi que son message
            loader.classList.add('not-visible');
            loaderP.classList.add('not-visible');
            successMessage.classList.remove('not-visible');
            setTimeout(() => {
                successMessage.classList.add('not-visible');
            }, 3000);
        } else {
            alert("Une erreur est survenue.");
        }
    }).catch(error => {
        console.error('Erreur:', error);
    });
}


// Rendre la fonction accessible globalement
window.sendPostRequest = sendPostRequest;
