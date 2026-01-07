<?php
session_start();
require_once __DIR__ . '/../../app/helpers/auth.php';
require_once __DIR__ . '/../../app/helpers/csrf.php';
require_once __DIR__ . '/../../app/models/AdminModel.php';

require_any_role(['admin', 'sekretariat']);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $login = trim($_POST['login'] ?? '');
    $haslo = trim($_POST['haslo'] ?? '');
    $imie = trim($_POST['imie'] ?? '');
    $nazwisko = trim($_POST['nazwisko'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefon = trim($_POST['telefon'] ?? '');
    $rola = $_POST['rola'] ?? 'nauczyciel';

    if ($login === '' || $haslo === '' || $imie === '' || $nazwisko === '' || $email === '') {
        $message = 'Uzupełnij wszystkie wymagane pola.';
    } else {
        $userId = AdminModel::addUser($pdo, [
            ':login' => $login,
            ':haslo' => password_hash($haslo, PASSWORD_DEFAULT),
            ':imie' => $imie,
            ':nazwisko' => $nazwisko,
            ':email' => $email,
            ':rola' => $rola,
            ':telefon' => $telefon ?: null,
        ]);
        if ($rola === 'nauczyciel') {
            AdminModel::addTeacher($pdo, $userId);
        }
        $message = 'Dodano pracownika.';
    }
}

$title = 'Dodaj pracownika';
require __DIR__ . '/../../templates/header.php';
?>
<div class="card">
    <h2>Dodaj pracownika</h2>
    <?php if ($message): ?>
        <p class="success"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
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
        <div class="field">
            <label for="imie">Imię</label>
            <input type="text" name="imie" id="imie" required>
        </div>
        <div class="field">
            <label for="nazwisko">Nazwisko</label>
            <input type="text" name="nazwisko" id="nazwisko" required>
        </div>
        <div class="field">
            <label for="email">Email</label>
            <input type="text" name="email" id="email" required>
        </div>
        <div class="field">
            <label for="telefon">Telefon</label>
            <input type="text" name="telefon" id="telefon">
        </div>
        <div class="field">
            <label for="rola">Rola</label>
            <select name="rola" id="rola">
                <option value="nauczyciel">nauczyciel</option>
                <option value="sekretariat">sekretariat</option>
                <option value="admin">admin</option>
            </select>
        </div>
        <button class="btn" type="submit">Dodaj</button>
    </form>
</div>
<?php require __DIR__ . '/../../templates/footer.php'; ?>
