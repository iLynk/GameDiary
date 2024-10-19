document.querySelectorAll('.like, .dislike').forEach(button => {
    button.addEventListener('click', function() {
        const reviewId = this.getAttribute('data-id');
        const type = this.getAttribute('data-type');

        fetch(`/vote/${reviewId}/${type}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mettre à jour le score dans le DOM
                    document.querySelector(`#review-score-${reviewId}`).textContent = data.score;
                } else {
                    alert(data.error);
                }
            })
            .catch(error => console.error('Error:', error));
    });
});
