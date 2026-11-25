<?php
// src/Controllers/MenuItemController.php — CRUD блюд (главная сущность Болаззат)
final class MenuItemController
{
    private MenuItemRepository $items;
    private RestaurantRepository $restaurants;
    private OrderRepository $orders;
    private View $view;
    private const PER_PAGE = 10;

    public function __construct()
    {
        $this->items       = new MenuItemRepository();
        $this->restaurants = new RestaurantRepository();
        $this->orders      = new OrderRepository();
        $this->view        = new View(BASE_PATH . '/views');
    }

    /** GET /menu-items — список с поиском, фильтрами, пагинацией */
    public function index(): void
    {
        $filters = [
            'q'             => (string)($_GET['q'] ?? ''),
            'restaurant_id' => (int)($_GET['restaurant_id'] ?? 0),
            'is_active'     => isset($_GET['is_active']) && $_GET['is_active'] !== '' 
                ? (string)$_GET['is_active'] 
                : '',
            'category'      => (string)($_GET['category'] ?? ''),
        ];
        $page   = (int)($_GET['page'] ?? 1);
        $result = $this->items->paginate($filters, $page, self::PER_PAGE);

        echo $this->view->render('layouts/main', [
            'title'   => 'Меню — блюда',
            'content' => $this->view->render('menu-items/index', [
                'e'           => fn($s) => $this->view->e($s),
                'url'         => fn($p) => $this->view->url($p),
                'result'      => $result,
                'filters'     => $filters,
                'restaurants' => $this->restaurants->all(),
                'categories'  => array_values(array_unique(
                    array_column($this->restaurants->all(), 'category')
                )),
            ]),
        ]);
    }

    /** GET /menu-items/create — пустая форма */
    public function create(): void
    {
        $this->renderForm($_POST ?? [], $_SESSION['errors'] ?? [], null);
        unset($_SESSION['errors']);
    }

    /** POST /menu-items/create — сохранить новое блюдо */
    public function store(): void
    {
        [$data, $errors] = $this->validate($_POST, null);
        if ($errors) {
            $this->renderForm($_POST, $errors, null, 422);
            return;
        }
        $id = $this->items->create($data);
        $this->redirect("/menu-items/{$id}?msg=created");
    }

    /** GET /menu-items/{id} — карточка блюда */
    public function show(int $id): void
    {
        $item = $this->items->find($id);
        if (!$item) {
            http_response_code(404);
            echo 'Блюдо не найдено';
            return;
        }
        echo $this->view->render('layouts/main', [
            'title'   => $item['name'],
            'content' => $this->view->render('menu-items/show', [
                'e'   => fn($s) => $this->view->e($s),
                'url' => fn($p) => $this->view->url($p),
                'item' => $item,
                'msg'  => $_GET['msg'] ?? '',
            ]),
        ]);
    }

    /** GET /menu-items/{id}/edit — форма с данными */
    public function edit(int $id): void
    {
        $item = $this->items->find($id);
        if (!$item) {
            http_response_code(404);
            echo 'Блюдо не найдено';
            return;
        }
        $this->renderForm($_POST ?: $item, $_SESSION['errors'] ?? [], $id);
        unset($_SESSION['errors']);
    }

    /** POST /menu-items/{id}/edit — обновить блюдо */
    public function update(int $id): void
    {
        $item = $this->items->find($id);
        if (!$item) {
            http_response_code(404);
            echo 'Блюдо не найдено';
            return;
        }
        [$data, $errors] = $this->validate($_POST, $id);
        if ($errors) {
            $this->renderForm($_POST, $errors, $id, 422);
            return;
        }
        $this->items->update($id, $data);
        $this->redirect("/menu-items/{$id}?msg=updated");
    }

    /** POST /menu-items/{id}/delete — удалить (только POST!) */
    public function delete(int $id): void
    {
        $item = $this->items->find($id);
        if (!$item) {
            http_response_code(404);
            echo 'Блюдо не найдено';
            return;
        }
        // Проверка FK до удаления — даём понятное сообщение, а не страницу 500
        if ($this->orders->hasOrderItems($id)) {
            $this->redirect("/menu-items/{$id}?msg=has_orders");
            return;
        }
        try {
            $this->items->delete($id);
        } catch (PDOException $e) {
            // 1451 — нарушение внешнего ключа: на блюдо ссылаются order_items
            if (($e->errorInfo[1] ?? null) === 1451) {
                $this->redirect("/menu-items/{$id}?msg=has_orders");
                return;
            }
            throw $e;
        }
        $this->redirect('/menu-items?msg=deleted');
    }

    /** GET /reports/categories — отчёт GROUP BY */
    public function report(): void
    {
        echo $this->view->render('layouts/main', [
            'title'   => 'Отчёт по категориям',
            'content' => $this->view->render('reports/categories', [
                'e'    => fn($s) => $this->view->e($s),
                'url'  => fn($p) => $this->view->url($p),
                'rows' => $this->orders->reportByCategory(),
            ]),
        ]);
    }

    // ===== Приватные методы =====

    /** Серверная проверка формы + правило с обращением к БД */
    private function validate(array $input, ?int $id): array
    {
        $v = new Validator($input);
        $v->required('name', 'Название')
          ->maxLength('name', 100, 'Название')
          ->required('price', 'Цена')
          ->number('price', 'Цена', 0.01)
          ->required('restaurant_id', 'Ресторан')
          ->maxLength('description', 255, 'Описание')
          ->maxLength('image', 500, 'URL изображения')
          ->in('is_active', ['0', '1'], 'Статус');

        // Правило 1: ресторан должен существовать
        $rid = (int)($input['restaurant_id'] ?? 0);
        if ($rid && !in_array($rid, $this->restaurants->ids(), true)) {
            $v->addError('restaurant_id', 'Ресторан не найден');
        }

        // Правило 2 (с обращением к БД): уникальность названия внутри ресторана
        if (!$v->fails() && $rid && $this->items->nameExists($rid, trim($input['name']), $id)) {
            $v->addError('name', 'В этом ресторане уже есть блюдо с таким названием');
        }

        $data = [
            'restaurant_id' => $rid,
            'name'          => trim((string)($input['name'] ?? '')),
            'description'   => trim((string)($input['description'] ?? '')) ?: null,
            'price'         => (float)($input['price'] ?? 0),
            'image'         => trim((string)($input['image'] ?? '')) ?: null,
            'is_active'     => (int)($input['is_active'] ?? 1),
        ];
        return [$data, $v->errors()];
    }

    /** Рендер формы создания/редактирования */
    private function renderForm(array $data, array $errors, ?int $id, int $code = 200): void
    {
        http_response_code($code);
        echo $this->view->render('layouts/main', [
            'title'   => $id ? 'Изменить блюдо' : 'Новое блюдо',
            'content' => $this->view->render('menu-items/form', [
                'e'           => fn($s) => $this->view->e($s),
                'url'         => fn($p) => $this->view->url($p),
                'data'        => $data,
                'errors'      => $errors,
                'id'          => $id,
                'restaurants' => $this->restaurants->all(),
            ]),
        ]);
    }

    /** Редирект (PRG — Post/Redirect/Get) */
    private function redirect(string $to): void
    {
        header('Location: ' . $this->view->url($to), true, 302);
        exit;
    }
}