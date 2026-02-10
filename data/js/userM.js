document.addEventListener('DOMContentLoaded', () => {
    const loading = document.getElementById('loading-overlay');

    document.querySelectorAll('form.safe-submit').forEach(form => {
        let isSubmitting = false;

        form.addEventListener('submit', function (e) {
            if (isSubmitting) {
                e.preventDefault();
                return;
            }

            isSubmitting = true;

            if (loading) {
                loading.style.display = 'flex';
            }

            form.querySelectorAll('button[type="submit"]').forEach(btn => {
                btn.disabled = true;
                btn.dataset.originalText = btn.innerText;
                btn.innerText = 'Processing...';
            });
        });
    });
});


window.addEventListener('scroll', () => {
    document.querySelector('header').classList.toggle('scrolled', window.scrollY > 20)
})
