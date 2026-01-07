<?php
session_start();
require_once __DIR__ . '/../app/helpers/auth.php';
require_once __DIR__ . '/../app/helpers/csrf.php';
require_once __DIR__ . '/../app/models/UserModel.php';

require_guest();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['haslo'] ?? '';

    if ($login === '' || $password === '') {
        $error = 'Podaj login i hasło.';
    } else {
        $user = UserModel::findByLogin($pdo, $login);
        if (!$user || !password_verify($password, $user['haslo'])) {
            $error = 'Nieprawidłowy login lub hasło.';
        } else {
            login_user($user);
            if (in_array($user['rola'], ['uczen', 'rodzic'], true)) {
                header('Location: /dziennik/index.php');
            } elseif ($user['rola'] === 'nauczyciel') {
                header('Location: /nauczyciel/index.php');
            } else {
                header('Location: /admin/index.php');
            }
            exit;
        }
    }
}

$title = 'Logowanie';
require __DIR__ . '/../templates/header.php';
?>
<div class="card">
    <h2>Logowanie</h2>
    <?php if ($error): ?>
        <p class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>
    <form method="post">
        <?php echo csrf_input(); ?>
        <div class="field">
            <label for="login">Login</label>
            <input type="text" name="login" id="login" required>
        </div>
        <div class="field">
            <label for="haslo">Hasło</label>
            <input type="password" name="haslo" id="haslo" required>
        </div>
        <button class="btn" type="submit">Zaloguj</button>
    </form>
</div>
<?php require __DIR__ . '/../templates/footer.php'; ?>
