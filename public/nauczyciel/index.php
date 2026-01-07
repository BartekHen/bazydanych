<?php
session_start();
require_once __DIR__ . '/../../app/helpers/auth.php';
require_once __DIR__ . '/../../app/models/TeacherModel.php';

require_role('nauczyciel');
$teacherId = TeacherModel::findTeacherId($pdo, current_user()['id_uzytkownika']);
if (!$teacherId) {
    http_response_code(403);
    echo 'Brak przypisanego nauczyciela.';
    exit;
}

$assignments = TeacherModel::assignments($pdo, $teacherId);
$selected = isset($_GET['pwk']) ? (int) $_GET['pwk'] : null;
$title = 'Panel nauczyciela';
require __DIR__ . '/../../templates/header.php';
?>
<div class="card">
    <h2>Panel nauczyciela</h2>
    <form method="get">
        <div class="field">
            <label for="pwk">Wybierz klasę i przedmiot</label>
            <select name="pwk" id="pwk" required>
                <?php foreach ($assignments as $assignment): ?>
                    <option value="<?php echo (int) $assignment['id_przedmiot_w_klasie']; ?>"
                        <?php echo $selected === (int) $assignment['id_przedmiot_w_klasie'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($assignment['klasa'] . ' - ' . $assignment['przedmiot'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="btn" type="submit">Przejdź</button>
    </form>
</div>

<?php if ($selected): ?>
    <div class="grid">
        <a class="tile" href="/nauczyciel/temat.php?pwk=<?php echo $selected; ?>"><strong>Temat lekcji</strong>Dodaj temat</a>
        <a class="tile" href="/nauczyciel/obecnosci.php?pwk=<?php echo $selected; ?>"><strong>Obecności</strong>Oznacz nieobecnych</a>
        <a class="tile" href="/nauczyciel/oceny.php?pwk=<?php echo $selected; ?>"><strong>Oceny</strong>Wystaw oceny</a>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/../../templates/footer.php'; ?>
