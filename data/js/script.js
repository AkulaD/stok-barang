document.addEventListener('DOMContentLoaded', () => {

    const form = document.querySelector('form');
    const loading = document.getElementById('loading-overlay');

    if (form && loading) {
        form.addEventListener('submit', function () {
            loading.style.display = 'flex';

            const btn = form.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.innerText = 'Processing...';
            }
        });
    }

    const toggle = document.querySelector('.nav-toggle');
    const navMobile = document.querySelector('.nav-mobile');

    if (toggle && navMobile) {
        toggle.addEventListener('click', () => {
            navMobile.classList.toggle('active');
        });
    }

    document.querySelectorAll(".dropdown > a").forEach(el => {
        el.addEventListener("click", e => {
            if (window.innerWidth <= 768) {
                e.preventDefault();

                const menu = el.nextElementSibling;

                document.querySelectorAll(".dropdown-menu").forEach(m => {
                    if (m !== menu) m.classList.remove("show");
                });

                if (menu) {
                    menu.classList.toggle("show");
                }
            }
        });
    });

});

window.addEventListener('scroll', () => {
    const header = document.querySelector('header');
    if (header) {
        header.classList.toggle('scrolled', window.scrollY > 20);
    }
});
