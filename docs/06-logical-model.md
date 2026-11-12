# Логическая модель данных: food_delivery

**Тема:** ИС «Доставка еды»  
**Автор:** Манучехр, группа [укажите]  
**Дата:** 12.11.2026  
**СУБД:** MySQL 8.0 (InnoDB, utf8mb4). Деньги — `DECIMAL(10,2)`, валюта TJS (сомони).

## 1. Исходная таблица (Excel оператора, ненормализованная форма)

Так сеть «Вкусно и быстро» ведёт журнал заказов сейчас. Данные вымышлены.

| № заказа | Дата/время | Клиент (ФИО) | Телефоны клиента | Адрес доставки | Ресторан | Адрес ресторана | Блюда | Сумма, TJS | Статус | Курьер / телефон |
|---|---|---|---|---|---|---|---|---|---|---|
| ORD-2026-00121 | 10.11.2026 12:15 | Каримов Далер | 92 111 22 33, 93 444 55 66 | ул. Рудаки 15, кв. 4 | Вкусно и быстро №1 | пр. И. Сомони 42 | Пицца Маргарита ×2 по 55; Кола 0.5 ×1 по 8 | 118 | доставлен 10.11 13:05 | Алиев Ф. / 90 555 11 22 |
| ORD-2026-00122 | 10.11.2026 12:40 | Каримов Далер | 92 111 22 33, 93 444 55 66 | ул. Рудаки 15, кв. 4 | Вкусно и быстро №1 | пр. И. Сомони 42 | Бургер Классик ×1 по 35; Картофель фри ×1 по 15 | 50 | в пути | Алиев Ф. / 90 555 11 22 |
| ORD-2026-00123 | 10.11.2026 13:05 | Назарова Мадина | 98 777 66 55 | ул. Гагарина 8, частный дом | Вкусно и быстро №3 | ул. Айни 12 | Салат Цезарь ×2 по 28; Чай зелёный ×2 по 5 | 66 | готовится | Саидов Р. / 91 222 33 44 |

**Аномалии, которые видны сразу:**
- **Изменение:** переименуем ресторан «№1» — придётся править все строки заказов (сотни строк).
- **Вставка:** нельзя добавить новое блюдо в меню, пока его никто не заказал.
- **Удаление:** удалим последний заказ курьера Саидова — потеряем его телефон.

## 2. Нормализация

### 1НФ — в каждой ячейке одно значение, нет повторяющихся групп

| Что нарушено в исходной таблице | Как исправлено |
|---|---|
| «Клиент (ФИО)» — составное значение | `clients.last_name`, `clients.first_name`, `clients.middle_name` |
| «Телефоны клиента» — два номера в одной ячейке | отдельная таблица `client_phones`: одна строка = один телефон |
| «Адрес доставки» — улица, дом, квартира в одной строке | `addresses.street`, `building`, `apartment` |
| «Блюда» — список из нескольких позиций с ценами | отдельная таблица `order_items`: одна строка = одно блюдо в заказе |
| «Статус» — статус и дата доставки склеены (`доставлен 10.11 13:05`) | `orders.status` (ENUM) + `orders.delivered_at` (DATETIME) |
| «Курьер / телефон» — два значения в ячейке | `couriers.full_name`, `couriers.phone`; в заказе только `orders.courier_id` |
| «Сумма» — вычисляемое значение (2×55 + 8 = 118) | не хранится, считается как `SUM(order_items.quantity * price)` |

### 2НФ — 1НФ + нет частичных зависимостей от составного ключа

После выделения позиций заказа естественный ключ таблицы `order_items` — составной:
`(order_id, dish_id)`.

| Зависимость от части ключа | Нарушение | Решение |
|---|---|---|
| `(order_id, dish_id)` → `dish_name`, `dish_category` | название и категория блюда зависят только от `dish_id` | вынесены в таблицу `dishes` |
| `(order_id, dish_id)` → `quantity`, `price` | зависят от **всего** ключа — это корректно | остаются в `order_items` |

Все остальные таблицы имеют простой суррогатный ключ `id`, частичных зависимостей нет.

> `order_items.price` — цена **на момент заказа** (исторический снимок), она не зависит от текущей `dishes.price`, поэтому дублированием не является.

### 3НФ — 2НФ + нет транзитивных зависимостей

| Транзитивная зависимость | Решение |
|---|---|
| заказ → ресторан → адрес ресторана, телефон ресторана | таблица `restaurants`, в заказе только `orders.restaurant_id` |
| заказ → клиент → ФИО, e-mail, телефоны | таблицы `clients` и `client_phones`, в заказе только `orders.client_id` |
| заказ → курьер → ФИО, телефон, транспорт | таблица `couriers`, в заказе только `orders.courier_id` |
| заказ → адрес доставки → координаты (lat, lon) | таблица `addresses`, в заказе только `orders.address_id` |
| блюдо → ресторан → адрес ресторана | `dishes.restaurant_id`, адрес хранится только в `restaurants` |

**Проверка 3НФ:** переименование ресторана меняет **одну** строку в `restaurants`, а не все строки заказов. Смена телефона курьера — одна строка в `couriers`. Итог заказа не хранится, поэтому не может «разойтись» с позициями.

## 3. Сущности

Всего 10 таблиц. Имена — английские, множественное число, `snake_case`. Внешние ключи — `<сущность>_id`.

### 3.1. users — учётные записи

| Атрибут | Тип | Ключ | NULL | Описание |
|---|---|---|---|---|
| id | INT AUTO_INCREMENT | PK | нет | |
| username | VARCHAR(50) | UQ | нет | логин |
| password_hash | VARCHAR(255) | | нет | хеш пароля (bcrypt) |
| role | ENUM('admin','manager','courier','client') | | нет | роль из лаб. 1, по умолчанию `client` |
| is_active | TINYINT(1) | | нет | по умолчанию 1 |
| created_at | DATETIME | | нет | дата регистрации |
| last_login_at | DATETIME | | да | последний вход |

### 3.2. clients — клиенты

| Атрибут | Тип | Ключ | NULL | Описание |
|---|---|---|---|---|
| id | INT | PK | нет | |
| user_id | INT | FK → users.id, UQ | да | учётная запись; NULL — заказ оформил оператор по телефону |
| last_name | VARCHAR(60) | | нет | фамилия |
| first_name | VARCHAR(60) | | нет | имя |
| middle_name | VARCHAR(60) | | да | отчество |
| phone | VARCHAR(20) | UQ | нет | основной телефон |
| email | VARCHAR(120) | UQ | да | e-mail |
| created_at | DATETIME | | нет | дата добавления |

### 3.3. client_phones — дополнительные телефоны клиента

| Атрибут | Тип | Ключ | NULL | Описание |
|---|---|---|---|---|
| id | INT | PK | нет | |
| client_id | INT | FK → clients.id | нет | владелец номера |
| phone | VARCHAR(20) | UQ(client_id, phone) | нет | один номер в строке (1НФ) |

### 3.4. addresses — адреса доставки

| Атрибут | Тип | Ключ | NULL | Описание |
|---|---|---|---|---|
| id | INT | PK | нет | |
| client_id | INT | FK → clients.id | нет | чей адрес |
| street | VARCHAR(150) | | нет | улица |
| building | VARCHAR(10) | | нет | дом |
| apartment | VARCHAR(10) | | да | квартира; NULL — частный дом |
| lat | DECIMAL(9,6) | | нет | широта для проверки зоны доставки |
| lon | DECIMAL(9,6) | | нет | долгота |
| comment | VARCHAR(255) | | да | подъезд, этаж, ориентиры |

### 3.5. restaurants — рестораны

| Атрибут | Тип | Ключ | NULL | Описание |
|---|---|---|---|---|
| id | INT | PK | нет | |
| name | VARCHAR(150) | UQ | нет | название точки |
| address | VARCHAR(200) | | нет | адрес кухни |
| phone | VARCHAR(20) | | нет | телефон |
| work_hours | VARCHAR(50) | | нет | например «09:00–23:00» |
| min_order_amount | DECIMAL(10,2) | | нет | минимальная сумма заказа, CHECK ≥ 0 |
| delivery_radius_km | DECIMAL(5,2) | | нет | радиус зоны доставки, CHECK > 0 |

### 3.6. dishes — блюда (меню)

| Атрибут | Тип | Ключ | NULL | Описание |
|---|---|---|---|---|
| id | INT | PK | нет | |
| restaurant_id | INT | FK → restaurants.id | нет | чьё меню |
| name | VARCHAR(150) | UQ(restaurant_id, name) | нет | название блюда |
| description | VARCHAR(500) | | да | описание |
| category | ENUM('pizza','burgers','salads','drinks','desserts') | | нет | категория меню |
| price | DECIMAL(10,2) | | нет | текущая цена, CHECK ≥ 0 |
| is_available | TINYINT(1) | | нет | наличие; по умолчанию 1 |

### 3.7. couriers — курьеры

| Атрибут | Тип | Ключ | NULL | Описание |
|---|---|---|---|---|
| id | INT | PK | нет | |
| user_id | INT | FK → users.id, UQ | да | учётная запись курьера |
| full_name | VARCHAR(150) | | нет | ФИО |
| phone | VARCHAR(20) | UQ | нет | телефон |
| transport | ENUM('foot','bike','car') | | нет | транспорт |
| is_busy | TINYINT(1) | | нет | занят; по умолчанию 0 |

### 3.8. orders — заказы

| Атрибут | Тип | Ключ | NULL | Описание |
|---|---|---|---|---|
| id | INT | PK | нет | суррогатный ключ |
| number | VARCHAR(30) | UQ | нет | естественный номер «ORD-2026-00121» |
| client_id | INT | FK → clients.id | нет | кто заказал |
| restaurant_id | INT | FK → restaurants.id | нет | какая кухня готовит |
| address_id | INT | FK → addresses.id | нет | куда доставить |
| courier_id | INT | FK → couriers.id | да | NULL, пока курьер не назначен |
| manager_id | INT | FK → users.id | да | оператор, подтвердивший заказ (лаб. 4) |
| status | ENUM('new','accepted','cooking','on_the_way','delivered','cancelled') | | нет | по умолчанию `new` |
| payment_method | ENUM('cash','card_online','alif','dc') | | нет | способ оплаты |
| comment | VARCHAR(255) | | да | пожелание клиента |
| created_at | DATETIME | | нет | дата оформления |
| accepted_at | DATETIME | | да | дата подтверждения |
| delivered_at | DATETIME | | да | дата вручения; CHECK ≥ created_at |

Итоговая сумма **не хранится** — вычисляется запросом:
```sql
SELECT o.number, SUM(oi.quantity * oi.price) AS total
FROM orders o JOIN order_items oi ON oi.order_id = o.id
GROUP BY o.id;
```

### 3.9. order_items — позиции заказа (таблица-связка M:N)

| Атрибут | Тип | Ключ | NULL | Описание |
|---|---|---|---|---|
| id | INT | PK | нет | суррогатный ключ |
| order_id | INT | FK → orders.id, UQ(order_id, dish_id) | нет | заказ |
| dish_id | INT | FK → dishes.id, UQ(order_id, dish_id) | нет | блюдо |
| quantity | INT | | нет | количество, CHECK > 0 |
| price | DECIMAL(10,2) | | нет | цена на момент заказа, CHECK ≥ 0 |

### 3.10. reviews — отзывы

| Атрибут | Тип | Ключ | NULL | Описание |
|---|---|---|---|---|
| id | INT | PK | нет | |
| order_id | INT | FK → orders.id, UQ | нет | один отзыв на один заказ |
| client_id | INT | FK → clients.id | нет | автор отзыва |
| rating | TINYINT | | нет | оценка, CHECK BETWEEN 1 AND 5 |
| comment | VARCHAR(500) | | да | текст отзыва |
| created_at | DATETIME | | нет | дата отзыва |

## 4. Связи

| Связь | Тип | Внешний ключ |
|---|---|---|
| users — clients (учётная запись клиента) | 1:1, необязательная | clients.user_id (UNIQUE, NULL) |
| users — couriers (учётная запись курьера) | 1:1, необязательная | couriers.user_id (UNIQUE, NULL) |
| clients — телефоны | 1:N | client_phones.client_id |
| clients — адреса | 1:N | addresses.client_id |
| clients — заказы | 1:N | orders.client_id |
| restaurants — блюда | 1:N | dishes.restaurant_id |
| restaurants — заказы | 1:N | orders.restaurant_id |
| addresses — заказы | 1:N | orders.address_id |
| couriers — заказы | 1:N, необязательная | orders.courier_id (NULL) |
| users — подтверждённые заказы | 1:N, необязательная | orders.manager_id (NULL) |
| **заказы — блюда** | **M:N через связку** | **order_items(order_id, dish_id)** |
| orders — отзыв | 1:1, необязательная | reviews.order_id (UNIQUE) |
| clients — отзывы | 1:N | reviews.client_id |

### Как разрешена связь M:N

Заказ содержит много блюд, и одно блюдо входит в разные заказы — это M:N. Напрямую в реляционной БД такая связь не реализуется, поэтому введена таблица-связка `order_items` с двумя внешними ключами и полезной нагрузкой (`quantity`, `price`):

```
orders ||--|{ order_items }|--|| dishes
```

Ограничение `UNIQUE(order_id, dish_id)` не даёт добавить одно блюдо в заказ дважды — вместо дубля увеличивается `quantity`.

## 5. Ограничения целостности

### UNIQUE (7)
1. `users.username`
2. `clients.user_id` — одна учётка на одного клиента
3. `clients.phone`, `clients.email`
4. `couriers.phone`, `couriers.user_id`
5. `restaurants.name`
6. `dishes(restaurant_id, name)` — в одном ресторане нет двух одинаковых блюд
7. `order_items(order_id, dish_id)`, `reviews.order_id`, `orders.number`

### CHECK (5)
1. `order_items.quantity > 0`
2. `order_items.price >= 0` и `dishes.price >= 0`
3. `reviews.rating BETWEEN 1 AND 5`
4. `orders.delivered_at IS NULL OR orders.delivered_at >= orders.created_at`
5. `restaurants.min_order_amount >= 0 AND restaurants.delivery_radius_km > 0`

> CHECK работает в MySQL начиная с 8.0.16; в более старых версиях проверку дублируем валидатором приложения.

### Правила удаления для каждого FK

| Внешний ключ | ON DELETE | Обоснование |
|---|---|---|
| clients.user_id → users.id | SET NULL | удалили учётку — карточка клиента и история заказов остаются |
| couriers.user_id → users.id | SET NULL | то же для курьера |
| client_phones.client_id → clients.id | CASCADE | телефон не существует без клиента (композиция) |
| addresses.client_id → clients.id | CASCADE | адрес не существует без клиента |
| dishes.restaurant_id → restaurants.id | RESTRICT | нельзя удалить ресторан, пока в меню есть блюда |
| orders.client_id → clients.id | RESTRICT | нельзя удалить клиента с заказами (финансовая история) |
| orders.restaurant_id → restaurants.id | RESTRICT |同上 |
| orders.address_id → addresses.id | RESTRICT | адрес используется в заказе |
| orders.courier_id → couriers.id | SET NULL | курьер уволился — заказ остаётся, назначение снимается |
| orders.manager_id → users.id | SET NULL | удалили оператора — заказ остаётся |
| order_items.order_id → orders.id | CASCADE | позиции уничтожаются вместе с заказом (композиция) |
| order_items.dish_id → dishes.id | RESTRICT | блюдо встречается в заказах, удалять нельзя — ставим `is_available = 0` |
| reviews.order_id → orders.id | CASCADE | отзыв не существует без заказа |
| reviews.client_id → clients.id | RESTRICT | авторство отзыва сохраняется |

## 6. Уточнения по сравнению с диаграммой классов (лаб. 5)

| Что уточнено | Почему |
|---|---|
| `Restaurant.rating`, `Courier.rating`, `Order.totalAmount` не хранятся | это вычисляемые значения (`AVG`, `SUM`); их хранение создаёт аномалии обновления. Считаются запросом или VIEW |
| `Client.fullName` разбит на `last_name`, `first_name`, `middle_name` | требование 1НФ: атомарные значения |
| Добавлены `users`, `client_phones` | требование лаб. 6: таблица `users`; телефоны — многозначный атрибут по 1НФ |
| `Order.status` и роли — ENUM со значениями лаб. 1/4 | согласованность моделей: `new → accepted → cooking → on_the_way → delivered / cancelled` |