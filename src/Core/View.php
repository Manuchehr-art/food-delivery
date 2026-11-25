<?php
// src/Core/View.php — рендер PHP-шаблонов с экранированием
final class View
{
    public function __construct(private string $path) {}

    public function render(string $template, array $data = []): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        require $this->path . '/' . $template . '.php';
        return ob_get_clean();
    }

    // Экранирование HTML (защита от XSS)
    public function e(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
    
    // Формирование URL с учётом base_url
    public function url(string $p): string { return ($GLOBALS['base_url'] ?? '') . $p; }
}