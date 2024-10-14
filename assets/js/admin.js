// assets/js/admin.js

const loader = document.querySelector('.loader > img')
const loaderP = document.querySelector('.loader > p')
const successMessage = document.querySelector('.success-message')

function sendPostRequest(url) {
    // on affiche le loader et son message
    loader.classList.remove('not-visible')
    loaderP.classList.remove('not-visible')

    fetch(url, {
        method: 'POST',
        headers: {
            // pour spécifier que la requête est en ajax
            'X-Requested-With': 'XMLHttpRequest'
        }
    }).then(response => {
        if (response.ok) {
            // on masque le loader ainsi que son message /
            loader.classList.add('not-visible')
            loaderP.classList.add('not-visible')
            successMessage.classList.remove('not-visible');
            setTimeout(()=>{
                successMessage.classList.add('not-visible')
            }, 3000)

        } else {
            alert("Une erreur est survenue.");
        }
    }).catch(error => {
        console.error('Erreur:', error);
    });
}

