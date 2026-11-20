# UI-kit: FoodGo (Болаззат)

**Figma:** https://www.figma.com/design/u36zb43xq9OsyxUPyXtPx5/bolazzat?node-id=0-1&t=mYGBZQnppsV3vTVN-1  
**Прототип:** https://www.figma.com/proto/u36zb43xq9OsyxUPyXtPx5/bolazzat?node-id=2-2&t=mYGBZQnppsV3vTVN-1

## 1. Сетка (Grid)
*   **Desktop (1440px):** 12 колонок × 88 px, Gutter 24 px, Margin auto. Контентная область 1320 px (соответствует `.container` Bootstrap).
*   **Mobile (390px):** 4 колонки, Margin 16 px, Gutter 16 px.
*   **Шаг отступов:** 4 / 8 / 16 / 24 / 32 / 48 px (кратность 4px).

## 2. Цвета (Color Styles)
Темная тема с фиолетовым фоном и яркими акцентами. Все цвета созданы как стили Figma и соответствуют переменным CSS.

| Стиль Figma | HEX | Переменная CSS | Назначение |
| :--- | :--- | :--- | :--- |
| `color/bg` | #150d26 | --bg | Основной фон страницы |
| `color/card` | rgba(255,255,255,.07) | --card | Фон карточек, панелей, drawer |
| `color/line` | rgba(255,255,255,.13) | --line | Рамки, разделители, границы |
| `color/text` | #f6f1ff | --tx | Основной текст, заголовки |
| `color/muted` | #a99fc4 | --mu | Вторичный текст, подписи, placeholder |
| `color/primary` | #ffb703 | --sf | Акцентные кнопки, цены, рейтинги, активные чипы |
| `color/success` | #3ddc97 | --mint | Статус "Доставлен", уведомления об успехе |
| `color/danger` | #ff5d73 | --co | Статус "Отменен", ошибки, бейдж корзины |

> **Примечание для Day 10:** В `style.css` эти цвета переопределяют стандартные переменные Bootstrap через `:root`.

## 3. Типографика (Text Styles)
Используются шрифты с полной поддержкой кириллицы (включая таджикские символы ӯ, ғ, ҳ, ҷ, қ).

| Стиль Figma | Шрифт | Размер / Межстрочный | Начертание | Где используется |
| :--- | :--- | :--- | :--- | :--- |
| `text/h1` | Unbounded | 48px / 1.05 | Bold | Главный заголовок Hero-секции |
| `text/h2` | Unbounded | 22px / 1.2 | Bold | Заголовки секций, названия ресторанов |
| `text/body` | Manrope | 16px / 1.5 | Medium | Основной контент, описания |
| `text/small` | Manrope | 14px / 1.4 | Regular | Подписи, мета-информация, футер |
| `text/button` | Manrope | 14px / 1.4 | Bold | Текст на кнопках, навигации |

## 4. Компоненты (Components)

### Button (Кнопка)
Высота 44px (доступность), радиус 99px (pill shape). Создан как компонент с вариантами (Variants).
*   **Type:** Primary (оранжевый градиент), Secondary (прозрачный с рамкой), Danger (розовый).
*   **State:** Default, Hover (`translateY(-2px)`), Active (`scale(.95)`).

### Input (Поле ввода)
Высота 44px, радиус 14px. Вертикальный Auto Layout (Label → Field → Helper/Error).
*   **Default:** Фон `rgba(255,255,255,.06)`, рамка `rgba(255,255,255,.13)`.
*   **Focus:** Рамка `#ffb703`, внешняя тень `0 0 0 3px #ffb70333`.
*   **Error:** Рамка `#ff5d73`, текст подсказки красным.

### RestaurantCard (Карточка ресторана/блюда)
Горизонтальный или вертикальный Auto Layout. Радиус 22px, фон `--card`, backdrop-filter blur(10px).
*   **Структура:** Изображение (object-fit cover) + Padding 16px.
*   **Контент:** Название (H2) + Рейтинг (★ Primary) | Описание (Small Muted) | Бейдж категории.
*   **Hover:** Подъем на 6px, поворот -0.4deg, рамка становится Primary.

### Badge (Статус заказа)
Радиус 99px, padding 3px 11px, font-size 12px Bold.
*   `accepted`: Фон `#ffb70322`, текст `#ffb703`
*   `delivered`: Фон `#3ddc9722`, текст `#3ddc97`
*   `cancelled`: Фон `#ff5d7322`, текст `#ff5d73`

## 5. Экраны (Screens)

### User Flow
![User Flow](screens/wireframe.png)
*Рис. 1. Пользовательский сценарий оформления заказа и регистрации.*

### Wireframes
![Wireframes](screens/wireframe.png)
*Рис. 2. Вайрфреймы основных экранов на сетке 12 колонок.*

### Mockups
![Mockups](screens/mockup.png)
*Рис. 3. Финальные макеты интерфейса с использованием компонентов и стилей.*

### UI-kit
![UI-kit](screens/ui-kit.png)
*Рис. 4. Набор цветов, типографики и базовых компонентов системы.*

## 6. Доступность (Accessibility)
*   ✅ Контраст текста: Белый (#f6f1ff) на фоне (#150d26) = 12.4:1 (норма > 4.5:1).
*   ✅ Высота интерактивных элементов ≥ 44px (кнопки, инпуты).
*   ✅ Статусы заказов передаются не только цветом, но и текстом (Badge + Steps tracker).
*   ✅ У всех полей ввода есть placeholder или label.