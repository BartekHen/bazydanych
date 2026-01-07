<?php
session_start();
require_once __DIR__ . '/../../app/helpers/auth.php';
require_once __DIR__ . '/../../app/helpers/csrf.php';
require_once __DIR__ . '/../../app/models/TeacherModel.php';
require_once __DIR__ . '/../../app/models/StudentModel.php';
require_once __DIR__ . '/../../app/models/AttendanceModel.php';

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
$lessonStmt = $pdo->prepare('SELECT id_lekcji, temat, data FROM lekcja WHERE id_przedmiot_w_klasie = :pwk ORDER BY data DESC');
$lessonStmt->execute([':pwk' => $pwk]);
$lessons = $lessonStmt->fetchAll();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $lessonId = (int) ($_POST['id_lekcji'] ?? 0);
    $absent = $_POST['absent'] ?? [];
    if ($lessonId === 0) {
        $message = 'Wybierz lekcję.';
    } else {
        AttendanceModel::deleteAbsencesForLesson($pdo, $lessonId);
        foreach ($absent as $studentId) {
            AttendanceModel::addAbsence($pdo, (int) $studentId, $lessonId);
        }
        $message = 'Zapisano obecności.';
    }
}

$title = 'Obecności';
require __DIR__ . '/../../templates/header.php';
?>
<div class="card">
    <h2>Obecności - <?php echo htmlspecialchars($assignment['klasa'] . ' / ' . $assignment['przedmiot'], ENT_QUOTES, 'UTF-8'); ?></h2>
    <?php if ($message): ?>
        <p class="success"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>
    <form method="post">
        <?php echo csrf_input(); ?>
        <div class="field">
            <label for="id_lekcji">Lekcja</label>
            <select name="id_lekcji" id="id_lekcji" required>
                <option value="">-- wybierz --</option>
                <?php foreach ($lessons as $lesson): ?>
                    <option value="<?php echo (int) $lesson['id_lekcji']; ?>">
                        <?php echo htmlspecialchars($lesson['data'] . ' - ' . $lesson['temat'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label>Nieobecni uczniowie</label>
            <?php foreach ($students as $student): ?>
                <div>
                    <label>
                        <input type="checkbox" name="absent[]" value="<?php echo (int) $student['id_ucznia']; ?>">
                        <?php echo htmlspecialchars($student['nr_dziennika'] . '. ' . $student['imie'] . ' ' . $student['nazwisko'], ENT_QUOTES, 'UTF-8'); ?>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
        <button class="btn" type="submit">Zapisz</button>
    </form>
</div>
<?php require __DIR__ . '/../../templates/footer.php'; ?>
