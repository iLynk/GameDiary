
function filterGames() {
    // Récupérer la valeur du champ de recherche
    let input = document.getElementById('search-game');
    let filter = input.value.toLowerCase();

    // Sélectionner tous les jeux
    let games = document.querySelectorAll('.game');

    // Parcourir tous les jeux et filtrer en fonction du nom
    games.forEach(function (game) {
        let gameName = game.querySelector('.game-name').textContent.toLowerCase();
        if (gameName.includes(filter)) {
            game.style.display = "block"; // Afficher le jeu
        } else {
            game.style.display = "none";  // Masquer le jeu
        }
    });
}

function filterCategories(categoryName) {
    // Récupérer tous les jeux
    let games = document.querySelectorAll('.game');

    // Parcourir chaque jeu pour vérifier ses catégories
    games.forEach(function(game) {
        // Récupérer les catégories du jeu
        let categories = game.getAttribute("data-categories");

        // Si le jeu appartient à la catégorie sélectionnée, l'afficher, sinon le masquer
        if (categories.includes(categoryName)) {
            game.style.display = "block"; // Afficher le jeu
        } else {
            game.style.display = "none";  // Masquer le jeu
        }
    });
}


