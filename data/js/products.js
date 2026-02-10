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

        if (loading) loading.style.display = 'flex';

        const btn = form.querySelector('button[type="submit"]');
        if (btn) {
            btn.disabled = true;
            btn.innerText = 'Processing...';
        }

        setTimeout(() => form.submit(), 100);
    });
});

window.addEventListener('scroll', () => {
    const header = document.querySelector('header');
    if(header){
        header.classList.toggle('scrolled', window.scrollY > 20);
    }
});

document.addEventListener('DOMContentLoaded', function () {

    const qrInput = document.getElementById('qr_code_input');
    const nameSelect = document.getElementById('product_name_select');

    if (!qrInput || !nameSelect) return;

    qrInput.addEventListener('input', function () {
        const val = this.value.trim();
        let found = false;

        for (let opt of nameSelect.options) {
            if (opt.dataset.qr && opt.dataset.qr.trim() === val) {
                nameSelect.value = opt.value;
                found = true;
                break;
            }
        }

        if (!found) nameSelect.value = "";
    });

    nameSelect.addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        qrInput.value = opt.dataset.qr ? opt.dataset.qr.trim() : "";
    });

});

    function generateColors(count) {
        return Array.from({ length: count }, (_, i) =>
            `hsl(${(360 / count) * i}, 70%, 55%)`
        );
    }

    function drawPie(canvasId, data) {
        const canvas = document.getElementById(canvasId);
        if(!canvas) return;
        const ctx = canvas.getContext('2d');
        const colors = generateColors(data.length);
        const total = data.reduce((s, d) => s + d.value, 0);
        const cx = canvas.width / 2;
        const cy = canvas.height / 2;
        const radius = 120;
        let angle = 0;
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        data.forEach((d, i) => {
            const slice = (d.value / total) * Math.PI * 2;
            ctx.beginPath();
            ctx.moveTo(cx, cy);
            ctx.arc(cx, cy, radius, angle, angle + slice);
            ctx.fillStyle = colors[i];
            ctx.fill();
            d.color = colors[i];
            angle += slice;
        });
    }

function renderOverlayList(data) {
    const box = document.getElementById('stockOverlay');
    if(!box) return;
    let html = `
        <div style="width:220px; max-height:220px; overflow-y:auto; background:#fff; border:1px solid #ddd; padding:10px; box-shadow:0 4px 10px rgba(0,0,0,.15); border-radius:6px; font-size:13px">
        <strong>List Stok</strong>
        <ul style="list-style:none;padding:0;margin:8px 0 0 0">
    `;
    data.forEach(d => {
        html += `
            <li style="display:flex;align-items:center;margin-bottom:6px">
                <span style="width:12px; height:12px; background:${d.color}; display:inline-block; margin-right:8px; border-radius:3px"></span>
                ${d.label} (${d.value})
            </li>
        `;
    });
    html += "</ul></div>";
    box.innerHTML = html;
}

drawPie('chartStock', chartStock);
renderOverlayList(chartStock);

const searchInput = document.getElementById('productSearch');
const rows = document.querySelectorAll('#productTableBody tr');

if(searchInput) {
    searchInput.addEventListener('keyup', () => {
        const keyword = searchInput.value.toLowerCase();
        rows.forEach(row => {
            const productNameElement = row.querySelector('.t-name');
            if(productNameElement) {
                const productName = productNameElement.textContent.toLowerCase();
                row.style.display = productName.includes(keyword) ? '' : 'none';
            }
        });
    });
}