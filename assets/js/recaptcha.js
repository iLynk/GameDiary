document.addEventListener('DOMContentLoaded', function () {
    // Sélectionne le formulaire et le champ hidden du reCAPTCHA
    const recaptchaField = document.querySelector('input[name="recaptcha"]');

    // Appelle le reCAPTCHA lorsque le formulaire est chargé
    if (recaptchaField) {
        grecaptcha.ready(function () {
            grecaptcha.execute('6LfpDW0qAAAAAEBP3_R2TRSQACfj53w2tXD6gMhj', {action: 'registration'}).then(function (token) {
                recaptchaField.value = token;
            });
        });
    }
});
