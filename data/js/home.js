const form = document.querySelector('form');
const loading = document.getElementById('loading-overlay');

form.addEventListener('submit', function () {
    loading.style.display = 'flex';

    const btn = form.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerText = 'Processing...';
});

window.addEventListener('scroll', () => {
    document.querySelector('header').classList.toggle('scrolled', window.scrollY > 20)
})

document.addEventListener('DOMContentLoaded', function () {
    const qrInput = document.getElementById('qr_number');
    const nameSelect = document.getElementById('name');

    function findByQR(qr){
        qr = qr.trim();
        for(let opt of nameSelect.options){
            if(opt.dataset.qr && opt.dataset.qr.trim() === qr){
                return opt;
            }
        }
        return null;
    }

    qrInput.addEventListener('input', function(){
        const opt = findByQR(this.value);
        if(opt){
            nameSelect.value = opt.value;
        }
    });

    nameSelect.addEventListener('change', function(){
        const opt = this.options[this.selectedIndex];
        if(opt.dataset.qr){
            qrInput.value = opt.dataset.qr.trim();
        }
    });
});