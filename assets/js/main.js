// assets/js/main.js

// Quantity controls
document.querySelectorAll('.qty-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const input = this.closest('.qty-group').querySelector('.qty-input');
        let val = parseInt(input.value) || 1;
        if (this.dataset.action === 'inc') val++;
        if (this.dataset.action === 'dec' && val > 1) val--;
        input.value = val;
    });
});

// Confirm delete
document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', function (e) {
        if (!confirm(this.dataset.confirm || 'Are you sure?')) e.preventDefault();
    });
});

// Auto dismiss alerts
setTimeout(() => {
    document.querySelectorAll('.alert').forEach(a => {
        a.style.transition = 'opacity .5s';
        a.style.opacity = '0';
        setTimeout(() => a.remove(), 500);
    });
}, 4000);

// Cart AJAX add
document.querySelectorAll('.btn-add-cart').forEach(btn => {
    btn.addEventListener('click', async function () {
        const productId = this.dataset.id;
        const qty = this.closest('.product-action')?.querySelector('.qty-input')?.value || 1;
        try {
            const res = await fetch('/cart/cart_backend.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=add&product_id=${productId}&quantity=${qty}`
            });
            const data = await res.json();
            if (data.success) {
                showToast(data.message, 'success');
                const badge = document.querySelector('.cart-badge');
                if (badge && data.cart_count !== undefined) badge.textContent = data.cart_count;
            } else {
                showToast(data.message, 'danger');
            }
        } catch (err) {
            showToast('Something went wrong', 'danger');
        }
    });
});

function showToast(msg, type = 'success') {
    const t = document.createElement('div');
    t.className = `alert alert-${type}`;
    t.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;min-width:250px;';
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(() => { t.style.opacity = '0'; t.style.transition = 'opacity .5s'; setTimeout(() => t.remove(), 500); }, 3000);
}
