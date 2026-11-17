<?php
namespace App\Core;

class View
{
    public static function render(string $template, array $data = [], string $layout = 'layouts/main'): void
    {
        // Извлекаем переменные для шаблона
        extract($data);

        // Буферизируем вывод шаблона
        $templatePath = __DIR__ . '/../Views/' . ltrim($template, '/') . '.php';
        if (!file_exists($templatePath)) {
            throw new \RuntimeException("Шаблон [{$templatePath}] не найден.");
        }

        ob_start();
        require $templatePath;
        $content = ob_get_clean();

        // Если layout не указан, просто выводим контент
        if (!$layout) {
            echo $content;
            return;
        }

        $layoutPath = __DIR__ . '/../Views/' . ltrim($layout, '/') . '.php';
        if (!file_exists($layoutPath)) {
            throw new \RuntimeException("Layout [{$layoutPath}] не найден.");
        }

        require $layoutPath;
    }

    public static function e(?string $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }

    public static function price(float|int|string|null $amount): string
    {
        $val = (float)($amount ?? 0);
        return number_format($val, 2, '.', ' ') . ' TJS';
    }

    public static function statusBadge(string $status): string
    {
        $map = [
            'new' => ['label' => 'Новый', 'tajik' => 'Нав', 'class' => 'bg-primary'],
            'accepted' => ['label' => 'Принят', 'tajik' => 'Қабул шуд', 'class' => 'bg-info text-dark'],
            'cooking' => ['label' => 'Готовится', 'tajik' => 'Дар ошхона', 'class' => 'bg-warning text-dark'],
            'on_the_way' => ['label' => 'В пути', 'tajik' => 'Дар роҳ', 'class' => 'bg-purple text-white'],
            'delivered' => ['label' => 'Доставлен', 'tajik' => 'Дастрас шуд', 'class' => 'bg-success'],
            'cancelled' => ['label' => 'Отменён', 'tajik' => 'Бекор шуд', 'class' => 'bg-danger'],
        ];

        $info = $map[$status] ?? ['label' => $status, 'tajik' => $status, 'class' => 'bg-secondary'];
        return sprintf(
            '<span class="badge %s" title="%s">%s</span>',
            $info['class'],
            htmlspecialchars($info['tajik'], ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($info['label'], ENT_QUOTES, 'UTF-8')
        );
    }

    public static function paymentBadge(string $method): string
    {
        $map = [
            'cash' => ['label' => 'Наличные (Нақд)', 'icon' => 'bi-cash'],
            'card_online' => ['label' => 'Карта (Online)', 'icon' => 'bi-credit-card'],
            'alif' => ['label' => 'Alif Mobi (QR)', 'icon' => 'bi-qr-code'],
            'dc' => ['label' => 'Dushanbe City (DC)', 'icon' => 'bi-wallet2'],
        ];
        $info = $map[$method] ?? ['label' => $method, 'icon' => 'bi-credit-card'];
        return sprintf(
            '<span class="badge bg-light text-dark border"><i class="bi %s me-1 text-primary"></i>%s</span>',
            $info['icon'],
            htmlspecialchars($info['label'], ENT_QUOTES, 'UTF-8')
        );
    }
}
