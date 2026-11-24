// public/assets/js/app.js — интерактивность FoodGo (Болаззат)
// Подключается как ES-модуль: <script type="module" src="../assets/js/app.js"></script>

console.log("✅ Скрипт app.js успешно загружен!");

// ===== Утилиты =====

// Откладывает вызов fn, пока пользователь не сделает паузу delay мс
function debounce(fn, delay = 300) {
  let timerId;
  return (...args) => {
    clearTimeout(timerId);
    timerId = setTimeout(() => fn(...args), delay);
  };
}

// 'YYYY-MM-DD' -> 'DD.MM.YYYY' (пустое значение -> '')
function formatDate(isoDate) {
  if (!isoDate) return '';
  const [year, month, day] = isoDate.split('-');
  return `${day}.${month}.${year}`;
}

// ===== Валидация формы заказа =====

const FIELD_MESSAGES = {
  restaurant_id: { valueMissing: 'Выберите ресторан.' },
  address: { valueMissing: 'Укажите адрес доставки.' },
  phone: { valueMissing: 'Укажите телефон.' },
  delivery_date: { valueMissing: 'Укажите дату доставки.' },
  delivery_time: { valueMissing: 'Укажите время доставки.' },
};

function messageFor(field) {
  const v = field.validity;
  if (v.valid) return '';
  if (v.customError) return field.validationMessage; // наше правило через setCustomValidity()
  const custom = FIELD_MESSAGES[field.name] ?? {};
  if (v.valueMissing) return custom.valueMissing ?? 'Заполните это поле.';
  if (v.tooLong) return `Не более ${field.maxLength} символов.`;
  if (v.typeMismatch) return 'Неверный формат.';
  if (v.patternMismatch) return 'Неверный формат телефона (например, +992 90 123 45 67).';
  return 'Проверьте значение поля.';
}

function showFieldState(field) {
  const message = messageFor(field);
  const feedback = document.getElementById(`${field.id}-error`);
  field.classList.toggle('is-invalid', message !== '');
  if (message) field.setAttribute('aria-invalid', 'true');
  else field.removeAttribute('aria-invalid');
  if (feedback) feedback.textContent = message;
  return message === '';
}

// Собственное правило: сумма заказа должна быть ≥ 30 сомони
function checkOrderTotal(form) {
  const totalElement = document.getElementById('order-total');
  const totalText = totalElement?.textContent ?? '0.00';
  const total = parseFloat(totalText.replace(/[^\d.,]/g, '').replace(',', '.'));
  const address = form.elements.address;
  address.setCustomValidity(''); // сбрасываем прошлую ошибку
  if (total < 30) {
    address.setCustomValidity(`Минимальная сумма заказа 30 сомони. Сейчас: ${total.toFixed(2)} с.`);
  }
}

function initOrderForm() {
  const form = document.getElementById('order-form');
  if (!form) return; // на этой странице формы нет

  const { delivery_date: dateField, delivery_time: timeField } = form.elements;
  const fields = () => form.querySelectorAll('input, select, textarea');

  // Подсказка: минимальная дата — сегодня
  const today = new Date().toISOString().split('T')[0];
  dateField.min = today;

  const refresh = (field) => {
    checkOrderTotal(form);
    showFieldState(field);
  };

  // focusout всплывает (в отличие от blur) — один обработчик на всю форму
  form.addEventListener('focusout', (event) => {
    if (event.target.matches('input, select, textarea')) {
      refresh(event.target);
    }
  });

  form.addEventListener('input', (event) => {
    if (event.target.classList.contains('is-invalid')) {
      refresh(event.target);
    }
  });

  form.addEventListener('submit', (event) => {
    checkOrderTotal(form);
    let firstInvalid = null;
    fields().forEach((field) => {
      if (!showFieldState(field) && !firstInvalid) firstInvalid = field;
    });
    if (firstInvalid) {
      event.preventDefault(); // не отправляем форму с ошибками
      firstInvalid.focus();
    }
  });
}

// ===== Живой поиск ресторанов =====

function createCell(text, className = '') {
  const td = document.createElement('td');
  td.textContent = text ?? ''; // textContent: любые <>& из данных выводятся как текст
  if (className) td.className = className;
  return td;
}

function createStatusBadge(isOpen) {
  const td = document.createElement('td');
  const badge = document.createElement('span');
  badge.className = `badge ${isOpen ? 'bg-success' : 'bg-danger'}`;
  badge.textContent = isOpen ? 'Открыто' : 'Закрыто';
  td.append(badge);
  return td;
}

function createRestaurantRow(restaurant) {
  const tr = document.createElement('tr');

  // Ячейка с названием (ссылка)
  const nameCell = document.createElement('td');
  const link = document.createElement('a');
  link.href = `restaurant.html?id=${encodeURIComponent(restaurant.id)}`;
  link.textContent = restaurant.name; // Безопасный вывод (защита от XSS)
  nameCell.append(link);

  // Ячейка с кнопкой "Открыть"
  const actionCell = document.createElement('td');
  actionCell.className = 'text-end';
  const actionLink = document.createElement('a');
  actionLink.href = `restaurant.html?id=${encodeURIComponent(restaurant.id)}`;
  actionLink.className = 'btn btn-sm btn-outline-light';
  actionLink.textContent = 'Открыть';
  actionCell.append(actionLink);

  // Собираем строку. Добавляем класс 'text-white' для нужных колонок
  tr.append(
    nameCell,
    createCell(restaurant.category, 'text-white'),
    createCell(`★ ${restaurant.rating}`, 'text-white'),
    createCell(`${restaurant.delivery_time} мин`, 'd-none d-md-table-cell text-white'),
    createStatusBadge(restaurant.is_open),
    actionCell
  );

  return tr;
}

function renderRestaurants(tbody, restaurants) {
  tbody.replaceChildren();
  if (restaurants.length === 0) {
    const tr = document.createElement('tr');
    const td = document.createElement('td');
    td.colSpan = 6;
    td.textContent = 'Рестораны не найдены. Попробуйте изменить запрос.';
    td.className = 'text-center text-muted py-4';
    tr.append(td);
    tbody.append(tr);
    return;
  }
  restaurants.forEach((r) => tbody.append(createRestaurantRow(r)));
}

function initRestaurantSearch() {
  const input = document.getElementById('restaurant-search');
  const categorySelect = document.getElementById('category');
  const tbody = document.getElementById('restaurants-tbody');
  if (!input || !tbody) return;

  const statusBox = document.getElementById('restaurant-search-status');
  const setStatus = (text) => { if (statusBox) statusBox.textContent = text; };

  let controller = null; // AbortController текущего запроса

  async function search(query, category) {
    controller?.abort(); // отменяем предыдущий запрос
    controller = new AbortController();
    tbody.setAttribute('aria-busy', 'true');
    setStatus('Поиск...');

    try {
      const params = new URLSearchParams();
      if (query) params.set('q', query);
      if (category) params.set('category', category);

      const response = await fetch(`/api/restaurants?${params.toString()}`, {
        headers: { Accept: 'application/json' },
        signal: controller.signal,
      });
      if (!response.ok) throw new Error(`HTTP ${response.status}`);

      const restaurants = await response.json();
      renderRestaurants(tbody, restaurants);
      setStatus(
        restaurants.length
          ? `Найдено ресторанов: ${restaurants.length}`
          : 'Рестораны не найдены.'
      );
    } catch (error) {
      if (error.name === 'AbortError') return; // запрос отменён более новым
      console.error('Ошибка живого поиска:', error);
      setStatus('Не удалось выполнить поиск. Проверьте соединение.');
    } finally {
      tbody.removeAttribute('aria-busy');
    }
  }

  const debouncedSearch = debounce(
    () => search(input.value.trim(), categorySelect.value),
    300
  );

  input.addEventListener('input', debouncedSearch);
  categorySelect.addEventListener('change', debouncedSearch);

  // Первоначальная загрузка
  search('', '');
}

// ===== Модальное окно подтверждения отмены =====

function initCancelConfirm() {
  const modalElement = document.getElementById('confirm-cancel-modal');
  if (!modalElement || !window.bootstrap) return;

  const form = document.getElementById('confirm-cancel-form');
  const nameElement = document.getElementById('confirm-cancel-name');
  const submitButton = form.querySelector('[type="submit"]');
  const modal = window.bootstrap.Modal.getOrCreateInstance(modalElement);
  let lastTrigger = null;

  // Делегирование: один обработчик на document ловит все кнопки с data-cancel-url
  document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-cancel-url]');
    if (!button) return;
    lastTrigger = button;
    form.action = button.dataset.cancelUrl;
    nameElement.textContent = button.dataset.orderName ?? '';
    submitButton.disabled = false;
    modal.show();
  });

  // После закрытия возвращаем фокус на кнопку (доступность)
  modalElement.addEventListener('hidden.bs.modal', () => {
    if (lastTrigger?.isConnected) lastTrigger.focus();
  });

  form.addEventListener('submit', () => {
    submitButton.disabled = true; // защита от двойного нажатия
  });
}

// ===== Запуск =====
// Модуль выполняется после разбора HTML (как defer), поэтому DOMContentLoaded не нужен.

initOrderForm();
initRestaurantSearch();
initCancelConfirm();