<?php
require_once __DIR__ . '/../../config/db.php';

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function current_role(): ?string
{
    return $_SESSION['user']['rola'] ?? null;
}

function require_login(): void
{
    if (!current_user()) {
        header('Location: /login.php');
        exit;
    }
}

function require_guest(): void
{
    if (current_user()) {
        header('Location: /home.php');
        exit;
    }
}

function require_role(string $role): void
{
    require_login();
    if (current_role() !== $role) {
        http_response_code(403);
        echo 'Brak dostępu.';
        exit;
    }
}

function require_any_role(array $roles): void
{
    require_login();
    if (!in_array(current_role(), $roles, true)) {
        http_response_code(403);
        echo 'Brak dostępu.';
        exit;
    }
}

function login_user(array $user): void
{
    $_SESSION['user'] = [
        'id_uzytkownika' => $user['id_uzytkownika'],
        'imie' => $user['imie'],
        'nazwisko' => $user['nazwisko'],
        'email' => $user['email'],
        'rola' => $user['rola'],
    ];
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}
