<?php
session_start();
require_once __DIR__ . '/../app/helpers/auth.php';

require_login();
$role = current_role();
$title = 'Panel startowy';
require __DIR__ . '/../templates/header.php';
?>
<div class="card">
    <h2>Witaj, <?php echo htmlspecialchars(current_user()['imie'], ENT_QUOTES, 'UTF-8'); ?>!</h2>
    <p>Wybierz moduł, aby kontynuować.</p>
</div>
<div class="grid">
    <?php if (in_array($role, ['uczen', 'rodzic'], true)): ?>
        <a class="tile" href="/dziennik/index.php"><strong>Dziennik</strong>Oceny, frekwencja, plan</a>
        <a class="tile" href="/sprawdziany/index.php"><strong>Sprawdziany</strong>Kalendarz sprawdzianów</a>
    <?php elseif ($role === 'nauczyciel'): ?>
        <a class="tile" href="/nauczyciel/index.php"><strong>Lekcje</strong>Tematy, oceny, obecności</a>
        <a class="tile" href="/sprawdziany/index.php"><strong>Sprawdziany</strong>Dodaj sprawdzian</a>
    <?php else: ?>
        <a class="tile" href="/admin/index.php"><strong>Administracja</strong>Użytkownicy i struktura</a>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/../templates/footer.php'; ?>
