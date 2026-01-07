<?php
session_start();
require_once __DIR__ . '/../../app/helpers/auth.php';
require_once __DIR__ . '/../../app/helpers/csrf.php';
require_once __DIR__ . '/../../app/models/TeacherModel.php';
require_once __DIR__ . '/../../app/models/TestsModel.php';

require_role('nauczyciel');
$teacherId = TeacherModel::findTeacherId($pdo, current_user()['id_uzytkownika']);
$assignments = TeacherModel::assignments($pdo, (int) $teacherId);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $pwk = (int) ($_POST['pwk'] ?? 0);
    $temat = trim($_POST['temat'] ?? '');
    $data = $_POST['data'] ?? '';
    $godzina = $_POST['godzina'] ?? '';

    $validAssignment = false;
    foreach ($assignments as $assignment) {
        if ((int) $assignment['id_przedmiot_w_klasie'] === $pwk) {
            $validAssignment = true;
            break;
        }
    }

    if (!$validAssignment || $temat === '' || $data === '' || $godzina === '') {
        $message = 'Uzupełnij wymagane pola.';
    } else {
        TestsModel::addTest($pdo, [
            'id_przedmiot_w_klasie' => $pwk,
            'temat' => $temat,
            'data' => $data,
            'godzina' => $godzina,
        ]);
        $message = 'Dodano sprawdzian.';
    }
}

$title = 'Dodaj sprawdzian';
require __DIR__ . '/../../templates/header.php';
?>
<div class="card">
    <h2>Dodaj sprawdzian</h2>
    <?php if ($message): ?>
        <p class="success"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>
    <form method="post">
        <?php echo csrf_input(); ?>
        <div class="field">
            <label for="pwk">Klasa i przedmiot</label>
            <select name="pwk" id="pwk" required>
                <?php foreach ($assignments as $assignment): ?>
                    <option value="<?php echo (int) $assignment['id_przedmiot_w_klasie']; ?>">
                        <?php echo htmlspecialchars($assignment['klasa'] . ' - ' . $assignment['przedmiot'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="temat">Temat</label>
            <input type="text" name="temat" id="temat" required>
        </div>
        <div class="field">
            <label for="data">Data</label>
            <input type="date" name="data" id="data" required>
        </div>
        <div class="field">
            <label for="godzina">Godzina</label>
            <input type="time" name="godzina" id="godzina" required>
        </div>
        <button class="btn" type="submit">Zapisz</button>
    </form>
</div>
<?php require __DIR__ . '/../../templates/footer.php'; ?>
