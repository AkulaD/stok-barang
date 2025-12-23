const form = document.querySelector('form');
const loading = document.getElementById('loading-overlay');

form.addEventListener('submit', function () {
    loading.style.display = 'flex';

    const btn = form.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerText = 'Processing...';
});
