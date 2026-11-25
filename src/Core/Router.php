<?php
// src/Core/Router.php — минимальный роутер GET/POST
final class Router
{
    private array $routes = ['get' => [], 'post' => []];

    public function get(string $pattern, array $handler): void  { $this->routes['get'][$pattern]  = $handler; }
    public function post(string $pattern, array $handler): void { $this->routes['post'][$pattern] = $handler; }

    public function dispatch(string $method, string $path): void
    {
        $method = strtolower($method);
        foreach ($this->routes[$method] as $pattern => $handler) {
            // Заменяем {id} на регулярное выражение для чисел
            $regex = '#^' . preg_replace('#\{([a-z_]+)\}#', '(?P<$1>[0-9]+)', $pattern) . '$#';
            if (preg_match($regex, $path, $m)) {
                $args = array_filter($m, 'is_string', ARRAY_FILTER_USE_KEY);
                [$class, $action] = $handler;
                (new $class())->$action(...array_values($args));
                return;
            }
        }
        http_response_code(404);
        echo 'Страница не найдена';
    }
}