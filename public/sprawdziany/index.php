<?php
session_start();
require_once __DIR__ . '/../../app/helpers/auth.php';
require_once __DIR__ . '/../../app/models/StudentModel.php';
require_once __DIR__ . '/../../app/models/TeacherModel.php';
require_once __DIR__ . '/../../app/models/TestsModel.php';

require_any_role(['uczen', 'rodzic', 'nauczyciel']);
$role = current_role();
$tests = [];
$selectedStudent = null;

if ($role === 'nauczyciel') {
    $teacherId = TeacherModel::findTeacherId($pdo, current_user()['id_uzytkownika']);
    $tests = TestsModel::testsForTeacher($pdo, (int) $teacherId);
} else {
    if ($role === 'uczen') {
        $student = StudentModel::findByUserId($pdo, current_user()['id_uzytkownika']);
        $selectedStudent = $student;
    } else {
        $children = StudentModel::childrenForParent($pdo, current_user()['id_uzytkownika']);
        $requestedId = isset($_GET['id_ucznia']) ? (int) $_GET['id_ucznia'] : null;
        foreach ($children as $child) {
            if ($requestedId && $child['id_ucznia'] === $requestedId) {
                $selectedStudent = $child;
            }
        }
        if (!$selectedStudent && $children) {
            $selectedStudent = $children[0];
        }
    }
    if ($selectedStudent && $selectedStudent['id_klasy']) {
        $tests = TestsModel::testsForClass($pdo, (int) $selectedStudent['id_klasy']);
    }
}

$title = 'Sprawdziany';
require __DIR__ . '/../../templates/header.php';
?>
<div class="card">
    <h2>Kalendarz sprawdzianów</h2>
    <?php if ($role === 'rodzic' && $selectedStudent): ?>
        <p>Dziecko: <?php echo htmlspecialchars($selectedStudent['imie'] . ' ' . $selectedStudent['nazwisko'], ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>
    <?php if ($role === 'nauczyciel'): ?>
        <a class="btn" href="/sprawdziany/dodaj.php">Dodaj sprawdzian</a>
    <?php endif; ?>
</div>

<?php if (!$tests): ?>
    <div class="card">Brak zaplanowanych sprawdzianów.</div>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <?php if ($role === 'nauczyciel'): ?>
                    <th>Klasa</th>
                <?php endif; ?>
                <th>Przedmiot</th>
                <th>Temat</th>
                <th>Data</th>
                <th>Godzina</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tests as $test): ?>
                <tr>
                    <?php if ($role === 'nauczyciel'): ?>
                        <td><?php echo htmlspecialchars($test['klasa'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <?php endif; ?>
                    <td><?php echo htmlspecialchars($test['przedmiot'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($test['temat'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($test['data'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($test['godzina'], ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
<?php require __DIR__ . '/../../templates/footer.php'; ?>
