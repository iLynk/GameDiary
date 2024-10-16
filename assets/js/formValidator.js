document.querySelector('form').addEventListener('submit', function(event) {
    // Vérification des champs e-mail et mot de passe
    let nameField = document.querySelector('input[name="user[name]"]');
    let passwordField = document.querySelector('input[name="user[password]"]');

    if (!nameField.value) {
        alert('L\'adresse e-mail est obligatoire.');
        event.preventDefault();  // Empêche la soumission du formulaire
        return;
    }

    if (passwordField && !passwordField.value) {
        alert('Le mot de passe est obligatoire.');
        event.preventDefault();  // Empêche la soumission du formulaire
    }

    // Ajout d'autres validations si nécessaire (longueur, format, etc.)
});