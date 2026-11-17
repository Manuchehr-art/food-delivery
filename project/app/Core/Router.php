<?php
namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, array|callable $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, array|callable $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    private function addRoute(string $method, string $path, array|callable $handler): void
    {
        // Преобразуем шаблон {param} в регулярное выражение (?P<param>[^/]+)
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';

        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'handler' => $handler,
        ];
    }

    public function dispatch(string $uri, string $method): void
    {
        // Очищаем URI от GET-параметров (?foo=bar) и лишних слэшей
        $path = parse_url($uri, PHP_URL_PATH);
        $path = '/' . trim($path, '/');
        if ($path === '//') {
            $path = '/';
        }

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $path, $matches)) {
                $params = array_filter($matches, '\is_string', ARRAY_FILTER_USE_KEY);
                $handler = $route['handler'];

                if (is_callable($handler)) {
                    call_user_func_array($handler, $params);
                    return;
                }

                if (is_array($handler) && count($handler) === 2) {
                    [$class, $action] = $handler;
                    if (class_exists($class)) {
                        $controller = new $class();
                        if (method_exists($controller, $action)) {
                            call_user_func_array([$controller, $action], $params);
                            return;
                        }
                    }
                }
            }
        }

        // Если маршрут не найден - 404
        http_response_code(404);
        View::render('home/404', ['title' => 'Страница не найдена - 404'], 'layouts/main');
    }
}
