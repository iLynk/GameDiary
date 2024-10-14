const form = document.querySelector('.add-review-form');
const formShowButton = document.querySelector('.show-review-form');
let showForm = false
const stars = document.querySelectorAll('.star');
const ratingInput = document.querySelector('input[name="review[rate]"]');


formShowButton.addEventListener('click', (e) => {
    showForm = !showForm;
    if(showForm){
        form.style.display = "block";
    }else{
        form.style.display = "none";
    }
})
stars.forEach(star => {
    star.addEventListener('click', function () {
        const rating = this.getAttribute('data-value');
        ratingInput.value = rating;

        stars.forEach(s => {
            s.classList.remove('selected');
            if (s.getAttribute('data-value') <= rating) {
                s.classList.add('selected');
            }
        });
    });
});