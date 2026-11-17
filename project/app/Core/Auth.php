<?php
namespace App\Core;

class Auth
{
    public static function login(array $user): void
    {
        Session::start();
        Session::set('user', [
            'id' => (int)$user['id'],
            'username' => $user['username'],
            'role' => $user['role'],
            'client_id' => $user['client_id'] ?? null,
            'client_name' => $user['client_name'] ?? null,
            'courier_id' => $user['courier_id'] ?? null,
        ]);
    }

    public static function logout(): void
    {
        Session::start();
        Session::remove('user');
    }

    public static function check(): bool
    {
        Session::start();
        return Session::has('user');
    }

    public static function user(): ?array
    {
        Session::start();
        return Session::get('user');
    }

    public static function id(): ?int
    {
        $u = self::user();
        return $u['id'] ?? null;
    }

    public static function role(): ?string
    {
        $u = self::user();
        return $u['role'] ?? null;
    }

    public static function clientId(): ?int
    {
        $u = self::user();
        return $u['client_id'] ?? null;
    }

    public static function courierId(): ?int
    {
        $u = self::user();
        return $u['courier_id'] ?? null;
    }

    public static function hasRole(string|array $roles): bool
    {
        if (!self::check()) {
            return false;
        }
        $r = self::role();
        if (is_array($roles)) {
            return in_array($r, $roles, true);
        }
        return $r === $roles;
    }

    public static function isAdmin(): bool
    {
        return self::hasRole('admin');
    }

    public static function isManager(): bool
    {
        return self::hasRole(['admin', 'manager']);
    }

    public static function isCourier(): bool
    {
        return self::hasRole('courier');
    }

    public static function isClient(): bool
    {
        return self::hasRole('client');
    }
}
