<?php
session_start();
require_once __DIR__ . '/../../app/helpers/auth.php';
require_once __DIR__ . '/../../app/helpers/csrf.php';
require_once __DIR__ . '/../../app/models/UserModel.php';

require_any_role(['admin', 'sekretariat']);
$userId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$user = $userId ? UserModel::findById($pdo, $userId) : null;

if (!$user) {
    http_response_code(404);
    echo 'Użytkownik nie istnieje.';
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    if ($action === 'role') {
        $role = $_POST['rola'] ?? $user['rola'];
        UserModel::updateRole($pdo, $userId, $role);
        $message = 'Zmieniono rolę.';
    } elseif ($action === 'password') {
        $newPassword = trim($_POST['new_password'] ?? '');
        if ($newPassword === '') {
            $message = 'Hasło nie może być puste.';
        } else {
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            UserModel::updatePassword($pdo, $userId, $hash);
            $message = 'Zresetowano hasło.';
        }
    } elseif ($action === 'deactivate') {
        UserModel::deactivate($pdo, $userId);
        $message = 'Dezaktywowano konto.';
    } elseif ($action === 'delete') {
        UserModel::delete($pdo, $userId);
        header('Location: /admin/uzytkownicy.php');
        exit;
    }
    $user = UserModel::findById($pdo, $userId);
}

$title = 'Edycja użytkownika';
require __DIR__ . '/../../templates/header.php';
?>
<div class="card">
    <h2>Edycja: <?php echo htmlspecialchars($user['imie'] . ' ' . $user['nazwisko'], ENT_QUOTES, 'UTF-8'); ?></h2>
    <?php if ($message): ?>
        <p class="success"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>
    <form method="post">
        <?php echo csrf_input(); ?>
        <input type="hidden" name="action" value="role">
        <div class="field">
            <label for="rola">Rola</label>
            <select name="rola" id="rola">
                <?php foreach (['uczen', 'rodzic', 'nauczyciel', 'admin', 'sekretariat'] as $rola): ?>
                    <option value="<?php echo $rola; ?>" <?php echo $user['rola'] === $rola ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($rola, ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="btn" type="submit">Zapisz rolę</button>
    </form>
</div>

<div class="card">
    <h3>Reset hasła</h3>
    <form method="post">
        <?php echo csrf_input(); ?>
        <input type="hidden" name="action" value="password">
        <div class="field">
            <label for="new_password">Nowe hasło</label>
            <input type="password" name="new_password" id="new_password" required>
        </div>
        <button class="btn" type="submit">Ustaw hasło</button>
    </form>
</div>

<div class="card">
    <h3>Akcje administracyjne</h3>
    <form method="post" style="display:inline-block;">
        <?php echo csrf_input(); ?>
        <input type="hidden" name="action" value="deactivate">
        <button class="btn btn-secondary" type="submit">Dezaktywuj</button>
    </form>
    <form method="post" style="display:inline-block;">
        <?php echo csrf_input(); ?>
        <input type="hidden" name="action" value="delete">
        <button class="btn btn-danger" type="submit" onclick="return confirm('Czy na pewno usunąć użytkownika?');">Usuń</button>
    </form>
</div>
<?php require __DIR__ . '/../../templates/footer.php'; ?>
