<?php
session_start();
require_once __DIR__ . '/../../app/helpers/auth.php';
require_once __DIR__ . '/../../app/helpers/csrf.php';
require_once __DIR__ . '/../../app/models/TeacherModel.php';
require_once __DIR__ . '/../../app/models/StudentModel.php';
require_once __DIR__ . '/../../app/models/GradesModel.php';

require_role('nauczyciel');
$teacherId = TeacherModel::findTeacherId($pdo, current_user()['id_uzytkownika']);
$pwk = isset($_GET['pwk']) ? (int) $_GET['pwk'] : 0;

$stmt = $pdo->prepare('SELECT pwk.id_przedmiot_w_klasie, pwk.id_klasy, k.nazwa AS klasa, p.nazwa AS przedmiot
                       FROM przedmiot_w_klasie pwk
                       JOIN klasa k ON k.id_klasy = pwk.id_klasy
                       JOIN przedmiot p ON p.id_przedmiotu = pwk.id_przedmiotu
                       WHERE pwk.id_przedmiot_w_klasie = :pwk AND pwk.id_nauczyciela = :nid');
$stmt->execute([':pwk' => $pwk, ':nid' => $teacherId]);
$assignment = $stmt->fetch();

if (!$assignment) {
    http_response_code(403);
    echo 'Brak dostępu do klasy.';
    exit;
}

$students = StudentModel::studentsByClass($pdo, (int) $assignment['id_klasy']);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $studentId = (int) ($_POST['id_ucznia'] ?? 0);
    $wartosc = trim($_POST['wartosc'] ?? '');
    $waga = (int) ($_POST['waga'] ?? 0);
    $typ = trim($_POST['typ'] ?? '');
    $data = $_POST['data'] ?? '';

    if (!$studentId || $wartosc === '' || $waga <= 0 || $data === '') {
        $message = 'Uzupełnij wszystkie wymagane pola.';
    } else {
        GradesModel::addGrade($pdo, [
            'id_ucznia' => $studentId,
            'wartosc' => $wartosc,
            'waga' => $waga,
            'typ' => $typ,
            'data_wystawienia' => $data,
            'id_przedmiot_w_klasie' => $pwk,
        ]);
        $message = 'Dodano ocenę.';
    }
}

$title = 'Oceny';
require __DIR__ . '/../../templates/header.php';
?>
<div class="card">
    <h2>Oceny - <?php echo htmlspecialchars($assignment['klasa'] . ' / ' . $assignment['przedmiot'], ENT_QUOTES, 'UTF-8'); ?></h2>
    <?php if ($message): ?>
        <p class="success"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>
    <form method="post">
        <?php echo csrf_input(); ?>
        <div class="field">
            <label for="id_ucznia">Uczeń</label>
            <select name="id_ucznia" id="id_ucznia" required>
                <?php foreach ($students as $student): ?>
                    <option value="<?php echo (int) $student['id_ucznia']; ?>">
                        <?php echo htmlspecialchars($student['nr_dziennika'] . '. ' . $student['imie'] . ' ' . $student['nazwisko'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="wartosc">Ocena</label>
            <input type="text" name="wartosc" id="wartosc" required>
        </div>
        <div class="field">
            <label for="waga">Waga</label>
            <input type="number" name="waga" id="waga" min="1" required>
        </div>
        <div class="field">
            <label for="typ">Typ</label>
            <input type="text" name="typ" id="typ">
        </div>
        <div class="field">
            <label for="data">Data</label>
            <input type="date" name="data" id="data" required>
        </div>
        <button class="btn" type="submit">Zapisz ocenę</button>
    </form>
</div>
<?php require __DIR__ . '/../../templates/footer.php'; ?>
