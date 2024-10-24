document.querySelectorAll('.like, .dislike').forEach(button => {
    button.addEventListener('click', function() {
        const reviewId = this.getAttribute('data-id');
        const type = parseInt(this.getAttribute('data-type'), 10); // Convertir en nombre entier
        const csrfToken = this.getAttribute('data-csrf');

        fetch(`/vote/${reviewId}/${type}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken // Ajout du token CSRF
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelector(`#review-score-${reviewId}`).textContent = 'Appréciation : ' + data.score;
                    updateVoteButtons(this, type, data.toggle); // Ajouter une information pour gérer le toggle
                } else {
                    alert(data.error);
                }
            })
            .catch(error => console.error('Error:', error));
    });
});

function updateVoteButtons(button, voteType, toggle) {
    const reviewElement = button.closest('.review'); // Récupère l'élément parent de la review
    const likeButton = reviewElement.querySelector('.like');
    const dislikeButton = reviewElement.querySelector('.dislike');

    // Réinitialiser les classes actives
    if (toggle) {
        // Si on a annulé le vote (toggle), on enlève les classes actives
        likeButton.classList.remove('liked');
        dislikeButton.classList.remove('disliked');
    } else {
        // Sinon, on met à jour les classes selon le type de vote
        likeButton.classList.remove('liked');
        dislikeButton.classList.remove('disliked');

        // Ajouter la classe active au bouton correspondant
        if (voteType === 1) {
            likeButton.classList.add('liked');
        } else if (voteType === -1) {
            dislikeButton.classList.add('disliked');
        }
    }
}
