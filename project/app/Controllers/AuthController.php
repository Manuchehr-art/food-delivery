<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\Client;
use App\Models\Courier;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            $this->redirectBasedOnRole(Auth::role());
        }

        $this->render('auth/login', [
            'title' => 'Вход в систему — Болаззат',
        ]);
    }

    public function login(): void
    {
        $this->requireCsrf();

        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($username) || empty($password)) {
            Session::setFlash('danger', 'Пожалуйста, заполните логин и пароль.');
            $this->redirect('/login');
        }

        $user = User::findByUsername($username);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            Session::setFlash('danger', 'Неверный логин или пароль.');
            $this->redirect('/login');
        }

        if (empty($user['is_active'])) {
            Session::setFlash('danger', 'Ваш аккаунт заблокирован.');
            $this->redirect('/login');
        }

        // Обновляем время входа
        User::updateLastLogin((int)$user['id']);

        // Аутентифицируем
        Auth::login($user);
        Session::setFlash('success', "Добро пожаловать, " . htmlspecialchars($user['username']) . "!");

        $this->redirectBasedOnRole($user['role']);
    }

    public function quickLogin(): void
    {
        $this->requireCsrf();
        $role = $_POST['role'] ?? 'client';

        $usernames = [
            'admin' => 'admin',
            'manager' => 'manager_anna',
            'courier' => 'courier_aliev',
            'client' => 'client_karimov',
        ];

        $targetUser = $usernames[$role] ?? 'admin';
        $user = User::findByUsername($targetUser);

        if ($user) {
            User::updateLastLogin((int)$user['id']);
            Auth::login($user);
            Session::setFlash('success', "Вы успешно вошли как [" . htmlspecialchars($user['role']) . "].");
            $this->redirectBasedOnRole($user['role']);
        }

        Session::setFlash('danger', 'Демо-пользователь не найден.');
        $this->redirect('/login');
    }

    public function showRegister(): void
    {
        if (Auth::check()) {
            $this->redirect('/');
        }

        $this->render('auth/register', [
            'title' => 'Регистрация клиента — Болаззат',
        ]);
    }

    public function register(): void
    {
        $this->requireCsrf();

        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (empty($username) || empty($password) || empty($firstName) || empty($lastName) || empty($phone)) {
            Session::setFlash('danger', 'Заполните все обязательные поля.');
            $this->redirect('/register');
        }

        if (User::findByUsername($username)) {
            Session::setFlash('danger', 'Такой логин уже занят. Выберите другой.');
            $this->redirect('/register');
        }

        try {
            $userId = User::create($username, $password, 'client');
            $clientId = Client::create([
                'user_id' => $userId,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => $phone,
                'email' => !empty($email) ? $email : null,
            ]);

            $user = User::findById($userId);
            Auth::login($user);

            Session::setFlash('success', 'Регистрация прошла успешно! Добро пожаловать!');
            $this->redirect('/');
        } catch (\Throwable $e) {
            Session::setFlash('danger', 'Ошибка при регистрации: ' . $e->getMessage());
            $this->redirect('/register');
        }
    }

    public function logout(): void
    {
        Auth::logout();
        Session::setFlash('info', 'Вы успешно вышли из системы.');
        $this->redirect('/');
    }

    private function redirectBasedOnRole(string $role): void
    {
        switch ($role) {
            case 'admin':
            case 'manager':
                $this->redirect('/admin');
                break;
            case 'courier':
                $this->redirect('/courier');
                break;
            default:
                $this->redirect('/');
                break;
        }
    }
}
