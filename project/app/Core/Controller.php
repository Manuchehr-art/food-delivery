<?php
namespace App\Core;

abstract class Controller
{
    protected function render(string $view, array $data = [], string $layout = 'layouts/main'): void
    {
        View::render($view, $data, $layout);
    }

    protected function json(mixed $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    protected function requireAuth(string|array|null $roles = null): void
    {
        if (!Auth::check()) {
            Session::setFlash('warning', 'Пожалуйста, войдите в систему.');
            $this->redirect('/login');
        }

        if ($roles !== null && !Auth::hasRole($roles)) {
            Session::setFlash('danger', 'У вас нет прав для доступа к этой странице.');
            $this->redirect('/');
        }
    }

    protected function requireCsrf(): void
    {
        $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!Csrf::validate($token)) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'error' => 'Ошибка безопасности (CSRF токен устарел)'], 403);
            }
            Session::setFlash('danger', 'Истекло время действия формы. Попробуйте еще раз.');
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/');
        }
    }

    protected function isAjax(): bool
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));
    }
}
