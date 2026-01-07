<?php
session_start();
require_once __DIR__ . '/../../app/helpers/auth.php';
require_once __DIR__ . '/../../app/models/StudentModel.php';

require_any_role(['uczen', 'rodzic']);
$role = current_role();
$children = [];

if ($role === 'rodzic') {
    $children = StudentModel::childrenForParent($pdo, current_user()['id_uzytkownika']);
}

$title = 'Dziennik';
require __DIR__ . '/../../templates/header.php';
?>
<div class="card">
    <h2>Dziennik</h2>
    <p>Wybierz moduł: oceny, obecności lub plan lekcji.</p>
</div>

<?php if ($role === 'rodzic' && $children): ?>
    <div class="card">
        <h3>Wybierz dziecko</h3>
        <form method="get" action="/dziennik/oceny.php">
            <div class="field">
                <label for="id_ucznia">Dziecko</label>
                <select name="id_ucznia" id="id_ucznia" required>
                    <?php foreach ($children as $child): ?>
                        <option value="<?php echo (int) $child['id_ucznia']; ?>">
                            <?php echo htmlspecialchars($child['imie'] . ' ' . $child['nazwisko'] . ' (' . $child['klasa'] . ')', ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button class="btn" type="submit">Przejdź do ocen</button>
        </form>
    </div>
<?php endif; ?>

<div class="grid">
    <a class="tile" href="/dziennik/oceny.php"><strong>Oceny</strong>Podgląd ocen ucznia</a>
    <a class="tile" href="/dziennik/obecnosci.php"><strong>Obecności</strong>Historia nieobecności</a>
    <a class="tile" href="/dziennik/plan.php"><strong>Plan lekcji</strong>Plan tygodniowy</a>
</div>
<?php require __DIR__ . '/../../templates/footer.php'; ?>
