<?php
session_start();
require_once __DIR__ . '/../../app/helpers/auth.php';
require_once __DIR__ . '/../../app/helpers/csrf.php';
require_once __DIR__ . '/../../app/models/AdminModel.php';

require_any_role(['admin', 'sekretariat']);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    if ($action === 'add_class') {
        $name = trim($_POST['nazwa'] ?? '');
        $stopien = (int) ($_POST['stopien'] ?? 1);
        if ($name !== '') {
            AdminModel::addClass($pdo, $name, $stopien);
            $message = 'Dodano klasę.';
        }
    } elseif ($action === 'add_subject') {
        $name = trim($_POST['nazwa'] ?? '');
        if ($name !== '') {
            AdminModel::addSubject($pdo, $name);
            $message = 'Dodano przedmiot.';
        }
    } elseif ($action === 'add_assignment') {
        $classId = (int) ($_POST['id_klasy'] ?? 0);
        $subjectId = (int) ($_POST['id_przedmiotu'] ?? 0);
        $teacherId = (int) ($_POST['id_nauczyciela'] ?? 0);
        $terminy = trim($_POST['terminy'] ?? '');
        if ($classId && $subjectId && $teacherId) {
            AdminModel::addAssignment($pdo, $classId, $subjectId, $teacherId, $terminy);
            $message = 'Dodano przypisanie.';
        }
    } elseif ($action === 'assign_student') {
        $classId = (int) ($_POST['id_klasy_uczen'] ?? 0);
        $studentId = (int) ($_POST['id_ucznia'] ?? 0);
        if ($classId && $studentId) {
            AdminModel::assignStudentToClass($pdo, $studentId, $classId);
            $message = 'Przypisano ucznia do klasy.';
        }
    }
}

$classes = AdminModel::allClasses($pdo);
$subjects = AdminModel::allSubjects($pdo);
$assignments = AdminModel::assignments($pdo);
$teachers = AdminModel::teachers($pdo);
$students = AdminModel::students($pdo);

$title = 'Klasy i przedmioty';
require __DIR__ . '/../../templates/header.php';
?>
<div class="card">
    <h2>Klasy i przedmioty</h2>
    <?php if ($message): ?>
        <p class="success"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>
</div>

<div class="card">
    <h3>Dodaj klasę</h3>
    <form method="post">
        <?php echo csrf_input(); ?>
        <input type="hidden" name="action" value="add_class">
        <div class="field">
            <label for="nazwa_klasy">Nazwa klasy</label>
            <input type="text" name="nazwa" id="nazwa_klasy" required>
        </div>
        <div class="field">
            <label for="stopien">Stopień</label>
            <input type="number" name="stopien" id="stopien" min="1" required>
        </div>
        <button class="btn" type="submit">Dodaj klasę</button>
    </form>
</div>

<div class="card">
    <h3>Dodaj przedmiot</h3>
    <form method="post">
        <?php echo csrf_input(); ?>
        <input type="hidden" name="action" value="add_subject">
        <div class="field">
            <label for="nazwa_przedmiotu">Nazwa przedmiotu</label>
            <input type="text" name="nazwa" id="nazwa_przedmiotu" required>
        </div>
        <button class="btn" type="submit">Dodaj przedmiot</button>
    </form>
</div>

<div class="card">
    <h3>Przypisania klas i przedmiotów</h3>
    <form method="post">
        <?php echo csrf_input(); ?>
        <input type="hidden" name="action" value="add_assignment">
        <div class="field">
            <label for="id_klasy">Klasa</label>
            <select name="id_klasy" id="id_klasy" required>
                <?php foreach ($classes as $class): ?>
                    <option value="<?php echo (int) $class['id_klasy']; ?>">
                        <?php echo htmlspecialchars($class['nazwa'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="id_przedmiotu">Przedmiot</label>
            <select name="id_przedmiotu" id="id_przedmiotu" required>
                <?php foreach ($subjects as $subject): ?>
                    <option value="<?php echo (int) $subject['id_przedmiotu']; ?>">
                        <?php echo htmlspecialchars($subject['nazwa'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="id_nauczyciela">Nauczyciel</label>
            <select name="id_nauczyciela" id="id_nauczyciela" required>
                <?php foreach ($teachers as $teacher): ?>
                    <option value="<?php echo (int) $teacher['id_nauczyciela']; ?>">
                        <?php echo htmlspecialchars($teacher['imie'] . ' ' . $teacher['nazwisko'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="terminy">Terminy (np. 1_3,2_5)</label>
            <input type="text" name="terminy" id="terminy">
        </div>
        <button class="btn" type="submit">Dodaj przypisanie</button>
    </form>
</div>

<div class="card">
    <h3>Przypisz ucznia do klasy</h3>
    <form method="post">
        <?php echo csrf_input(); ?>
        <input type="hidden" name="action" value="assign_student">
        <div class="field">
            <label for="id_ucznia">Uczeń</label>
            <select name="id_ucznia" id="id_ucznia" required>
                <?php foreach ($students as $student): ?>
                    <option value="<?php echo (int) $student['id_ucznia']; ?>">
                        <?php echo htmlspecialchars($student['imie'] . ' ' . $student['nazwisko'] . ' (' . ($student['klasa'] ?? 'brak klasy') . ')', ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="id_klasy_uczen">Klasa</label>
            <select name="id_klasy_uczen" id="id_klasy_uczen" required>
                <?php foreach ($classes as $class): ?>
                    <option value="<?php echo (int) $class['id_klasy']; ?>">
                        <?php echo htmlspecialchars($class['nazwa'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="btn" type="submit">Przypisz</button>
    </form>
</div>

<?php if ($assignments): ?>
    <table>
        <thead>
            <tr>
                <th>Klasa</th>
                <th>Przedmiot</th>
                <th>Nauczyciel</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($assignments as $assignment): ?>
                <tr>
                    <td><?php echo htmlspecialchars($assignment['klasa'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($assignment['przedmiot'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($assignment['imie'] . ' ' . $assignment['nazwisko'], ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
<?php require __DIR__ . '/../../templates/footer.php'; ?>
