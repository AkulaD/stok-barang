const loading = document.getElementById('loading-overlay');

document.querySelectorAll('form.safe-submit').forEach(form => {
    let isSubmitting = false;

    form.addEventListener('submit', function (e) {
        if (isSubmitting) {
            e.preventDefault();
            return;
        }

        e.preventDefault();
        isSubmitting = true;

        if (loading) {
            loading.style.display = 'flex';
        }

        const btn = form.querySelector('button[type="submit"]');
        if (btn) {
            btn.disabled = true;
            btn.innerText = 'Processing...';
        }

        setTimeout(() => {
            form.submit();
        }, 100);
    });
});

const toggle = document.querySelector('.nav-toggle');
const mobileNav = document.querySelector('.nav-mobile');

toggle.addEventListener('click', () => {
    mobileNav.classList.toggle('active');
});

window.addEventListener('scroll', () => {
    document.querySelector('header').classList.toggle('scrolled', window.scrollY > 20)
})