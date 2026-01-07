<?php
session_start();
require_once __DIR__ . '/../../app/helpers/auth.php';
require_once __DIR__ . '/../../app/helpers/csrf.php';
require_once __DIR__ . '/../../app/models/AdminModel.php';

require_any_role(['admin', 'sekretariat']);

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $parentId = (int) ($_POST['id_rodzica'] ?? 0);
    $studentId = (int) ($_POST['id_ucznia'] ?? 0);
    if ($parentId && $studentId) {
        AdminModel::assignParent($pdo, $parentId, $studentId);
        $message = 'Powiązano rodzica z uczniem.';
    } else {
        $message = 'Wybierz rodzica i ucznia.';
    }
}

$parents = AdminModel::parents($pdo);
$students = AdminModel::students($pdo);
$title = 'Panel sekretariatu - rodzice';
require __DIR__ . '/../../templates/header.php';
?>
<div class="card">
    <h2>Panel sekretariatu - rodzice</h2>
    <p>Twórz powiązania rodziców z uczniami.</p>
    <?php if ($message): ?>
        <p class="success"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>
    <form method="post">
        <?php echo csrf_input(); ?>
        <div class="field">
            <label for="id_rodzica">Rodzic</label>
            <select name="id_rodzica" id="id_rodzica" required>
                <option value="">-- wybierz --</option>
                <?php foreach ($parents as $parent): ?>
                    <option value="<?php echo (int) $parent['id_rodzica']; ?>">
                        <?php echo htmlspecialchars($parent['imie'] . ' ' . $parent['nazwisko'] . ' (' . $parent['email'] . ')', ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="id_ucznia">Uczeń</label>
            <select name="id_ucznia" id="id_ucznia" required>
                <option value="">-- wybierz --</option>
                <?php foreach ($students as $student): ?>
                    <option value="<?php echo (int) $student['id_ucznia']; ?>">
                        <?php echo htmlspecialchars($student['imie'] . ' ' . $student['nazwisko'] . ' (' . ($student['klasa'] ?? 'brak klasy') . ')', ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="btn" type="submit">Powiąż</button>
    </form>
</div>
<?php require __DIR__ . '/../../templates/footer.php'; ?>
