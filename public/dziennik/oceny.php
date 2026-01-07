<?php
session_start();
require_once __DIR__ . '/../../app/helpers/auth.php';
require_once __DIR__ . '/../../app/models/StudentModel.php';
require_once __DIR__ . '/../../app/models/GradesModel.php';

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

$grades = GradesModel::gradesForStudent($pdo, (int) $student['id_ucznia']);
$grouped = [];
foreach ($grades as $grade) {
    $grouped[$grade['przedmiot']][] = $grade;
}

$title = 'Oceny';
require __DIR__ . '/../../templates/header.php';
?>
<div class="card">
    <h2>Oceny</h2>
    <p>Uczeń: <?php echo htmlspecialchars($student['imie'] . ' ' . $student['nazwisko'], ENT_QUOTES, 'UTF-8'); ?></p>
</div>

<?php if (!$grades): ?>
    <div class="card">Brak ocen.</div>
<?php else: ?>
    <?php foreach ($grouped as $subject => $items): ?>
        <div class="card">
            <h3><?php echo htmlspecialchars($subject, ENT_QUOTES, 'UTF-8'); ?></h3>
            <table>
                <thead>
                    <tr>
                        <th>Ocena</th>
                        <th>Waga</th>
                        <th>Typ</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['wartosc'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo (int) $item['waga']; ?></td>
                            <td><?php echo htmlspecialchars($item['typ'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($item['data_wystawienia'], ENT_QUOTES, 'UTF-8'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php require __DIR__ . '/../../templates/footer.php'; ?>
