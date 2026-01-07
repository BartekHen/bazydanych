<?php
session_start();
require_once __DIR__ . '/../../app/helpers/auth.php';
require_once __DIR__ . '/../../app/models/StudentModel.php';
require_once __DIR__ . '/../../app/models/AttendanceModel.php';

require_any_role(['uczen', 'rodzic']);
$role = current_role();
$student = null;

if ($role === 'uczen') {
    $student = StudentModel::findByUserId($pdo, current_user()['id_uzytkownika']);
} else {
    $children = StudentModel::childrenForParent($pdo, current_user()['id_uzytkownika']);
    $requestedId = isset($_GET['id_ucznia']) ? (int) $_GET['id_ucznia'] : null;
    foreach ($children as $child) {
        if ($requestedId && $child['id_ucznia'] === $requestedId) {
            $student = $child;
        }
    }
    if (!$student && $children) {
        $student = $children[0];
    }
}

if (!$student) {
    http_response_code(404);
    echo 'Brak dostępu do ucznia.';
    exit;
}

$absences = AttendanceModel::absencesForStudent($pdo, (int) $student['id_ucznia']);

$title = 'Obecności';
require __DIR__ . '/../../templates/header.php';
?>
<div class="card">
    <h2>Nieobecności</h2>
    <p>Uczeń: <?php echo htmlspecialchars($student['imie'] . ' ' . $student['nazwisko'], ENT_QUOTES, 'UTF-8'); ?></p>
</div>

<?php if (!$absences): ?>
    <div class="card">Brak nieobecności.</div>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Przedmiot</th>
                <th>Temat</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($absences as $absence): ?>
                <tr>
                    <td><?php echo htmlspecialchars($absence['data'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($absence['przedmiot'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($absence['temat'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo $absence['usprawiedliwiona'] ? 'Usprawiedliwiona' : 'Nieusprawiedliwiona'; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require __DIR__ . '/../../templates/footer.php'; ?>
