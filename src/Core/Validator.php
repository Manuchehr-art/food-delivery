<?php
// src/Core/Validator.php — серверная проверка формы
final class Validator
{
    private array $errors = [];

    public function __construct(private array $input) {}

    public function required(string $field, string $label): self
    {
        if (trim((string)($this->input[$field] ?? '')) === '')
            $this->addError($field, "Поле «{$label}» обязательно");
        return $this;
    }

    public function maxLength(string $field, int $max, string $label): self
    {
        $v = (string)($this->input[$field] ?? '');
        if ($v !== '' && mb_strlen($v) > $max)
            $this->addError($field, "«{$label}»: не более {$max} символов");
        return $this;
    }

    public function number(string $field, string $label, float $min = 0): self
    {
        $v = $this->input[$field] ?? '';
        if ($v !== '' && (!is_numeric($v) || (float)$v < $min))
            $this->addError($field, "«{$label}»: должно быть числом ≥ {$min}");
        return $this;
    }

    public function in(string $field, array $allowed, string $label): self
    {
        $v = (string)($this->input[$field] ?? '');
        if ($v !== '' && !in_array($v, $allowed, true))
            $this->addError($field, "«{$label}»: недопустимое значение");
        return $this;
    }

    public function addError(string $field, string $message): self
    {
        $this->errors[$field] ??= $message;
        return $this;
    }

    public function errors(): array { return $this->errors; }
    public function fails(): bool   { return $this->errors !== []; }
}