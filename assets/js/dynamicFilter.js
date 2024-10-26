// Ce script gère tout le système de filtre (recherche aussi) de la page des jeux
function filterGames() {
    // On récupère le champ de recherche
    let input = document.getElementById('search-game');
    // et sa value
    let filter = input.value.toLowerCase();

    // On sélectionne tous les jeux
    let games = document.querySelectorAll('.game');

    // Parcourir tous les jeux et filtrer en fonction du nom
    games.forEach(function (game) {
        let gameName = game.querySelector('.game-name').textContent.toLowerCase();
        if (gameName.includes(filter)) {
            game.style.display = "block";
        } else {
            game.style.display = "none";
        }
    });
}

function filterCategories(categoryName) {
    // On sélectionne tous les jeux
    let games = document.querySelectorAll('.game');

    // Parcourir chaque jeu pour vérifier ses catégories
    games.forEach(function(game) {
        // Récupérer les catégories du jeu grace à l'attribut data-categories
        let categories = game.getAttribute("data-categories");

        // Si le jeu appartient à la catégorie sélectionnée, l'afficher, sinon le masquer
        if (categories.includes(categoryName)) {
            game.style.display = "block"; // Afficher le jeu
        } else {
            game.style.display = "none";  // Masquer le jeu
        }
    });
}

// fix qui empechait le lancement des fonctions, ces lignes sont utiles pour rendre les fonctions disponibles globalement
window.filterGames = filterGames;
window.filterCategories = filterCategories;
