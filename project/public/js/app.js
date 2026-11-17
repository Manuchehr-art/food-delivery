// public/js/app.js - Клиентский скрипт для корзины и интерактивных элементов

document.addEventListener('DOMContentLoaded', () => {
    // Обработка AJAX добавления в корзину
    document.querySelectorAll('.btn-add-cart-ajax').forEach(button => {
        button.addEventListener('click', async (e) => {
            e.preventDefault();
            const dishId = button.dataset.dishId;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            try {
                const formData = new FormData();
                formData.append('dish_id', dishId);
                formData.append('quantity', 1);
                formData.append('_csrf', csrfToken);

                const response = await fetch('/cart/add', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    // Обновляем бейдж корзины
                    document.querySelectorAll('.cart-badge-count').forEach(badge => {
                        badge.textContent = data.cart_count;
                        badge.classList.remove('d-none');
                    });
                    showToast(data.message, 'success');
                } else {
                    showToast(data.message || 'Ошибка добавления', 'warning');
                }
            } catch (err) {
                // Если не получилось через AJAX, отправляем стандартную форму
                const form = button.closest('form');
                if (form) form.submit();
            }
        });
    });

    // Функция показа уведомлений (Toast)
    function showToast(message, type = 'info') {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
        }

        const bgClass = type === 'success' ? 'bg-success text-white' : (type === 'danger' ? 'bg-danger text-white' : 'bg-dark text-white');
        const toastEl = document.createElement('div');
        toastEl.className = `toast align-items-center ${bgClass} border-0 shadow-lg mb-2`;
        toastEl.setAttribute('role', 'alert');
        toastEl.setAttribute('aria-live', 'assertive');
        toastEl.setAttribute('aria-atomic', 'true');
        toastEl.innerHTML = `
            <div class="d-flex">
                <div class="toast-body fw-semibold">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;

        container.appendChild(toastEl);
        if (window.bootstrap && bootstrap.Toast) {
            const toast = new bootstrap.Toast(toastEl, { delay: 3500 });
            toast.show();
            toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
        } else {
            setTimeout(() => toastEl.remove(), 3500);
        }
    }
});
