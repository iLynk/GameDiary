// Ce script est utilisé pour valider le formulaire lorsqu'on modifie ses informations
document.querySelector('form').addEventListener('submit', function(event) {
    // Vérification des champs e-mail et mot de passe
    let nameField = document.querySelector('input[name="user[name]"]');
    let passwordField = document.querySelector('input[name="user[password]"]');

    if (!nameField.value) {
        alert('L\'adresse e-mail est obligatoire.');
        event.preventDefault();  // On empêche la soumission du formulaire
        return;
    }

    if (passwordField && !passwordField.value) {
        alert('Le mot de passe est obligatoire.');
        event.preventDefault();  // On empêche la soumission du formulaire
    }
});