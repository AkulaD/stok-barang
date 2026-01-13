const form = document.querySelector('form');
const loading = document.getElementById('loading-overlay');

form.addEventListener('submit', function () {
    loading.style.display = 'flex';

    const btn = form.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerText = 'Processing...';
});


document.addEventListener('DOMContentLoaded', () => {

    const toggle = document.getElementById('menuToggle');
    const menu = document.getElementById('menu');

    if (toggle && menu) {
        toggle.addEventListener('click', () => {
            menu.classList.toggle('active');
        });
    }

});

window.addEventListener('scroll', () => {
    document.querySelector('header').classList.toggle('scrolled', window.scrollY > 20)
})