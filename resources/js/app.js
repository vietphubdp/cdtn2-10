// Bootstrap 5.3 JS (dropdown, modal, collapse...) – dùng JavaScript thuần, không jQuery
import * as bootstrap from 'bootstrap';

window.bootstrap = bootstrap;

// Nút −/+ cạnh ô số lượng (trang chi tiết SP): tăng/giảm trong khoảng min..max của ô nhập
document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-qty-step]');
    if (!button) {
        return;
    }

    const input = button.parentElement.querySelector('input[type="number"]');
    const min = Number(input.min) || 1;
    const max = Number(input.max) || Infinity;
    const next = (Number(input.value) || min) + Number(button.dataset.qtyStep);

    input.value = Math.min(max, Math.max(min, next));
});
