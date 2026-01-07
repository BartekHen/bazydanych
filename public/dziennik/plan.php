<?php
session_start();
require_once __DIR__ . '/../../app/helpers/auth.php';
require_once __DIR__ . '/../../app/models/StudentModel.php';
require_once __DIR__ . '/../../app/models/ScheduleModel.php';

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

if (!$student || !$student['id_klasy']) {
    http_response_code(404);
    echo 'Brak planu lekcji.';
    exit;
}

$schedule = ScheduleModel::scheduleForClass($pdo, (int) $student['id_klasy']);
$days = [1 => 'Poniedziałek', 2 => 'Wtorek', 3 => 'Środa', 4 => 'Czwartek', 5 => 'Piątek'];
$hours = range(1, 8);

$title = 'Plan lekcji';
require __DIR__ . '/../../templates/header.php';
?>
<div class="card">
    <h2>Plan lekcji</h2>
    <p>Uczeń: <?php echo htmlspecialchars($student['imie'] . ' ' . $student['nazwisko'], ENT_QUOTES, 'UTF-8'); ?></p>
</div>
<table>
    <thead>
        <tr>
            <th>Godzina</th>
            <?php foreach ($days as $dayName): ?>
                <th><?php echo htmlspecialchars($dayName, ENT_QUOTES, 'UTF-8'); ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($hours as $hour): ?>
            <tr>
                <td><?php echo $hour; ?></td>
                <?php foreach ($days as $dayIndex => $dayName): ?>
                    <td>
                        <?php
                        $subjects = $schedule[$dayIndex][$hour] ?? [];
                        echo htmlspecialchars(implode(', ', $subjects), ENT_QUOTES, 'UTF-8');
                        ?>
                    </td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php require __DIR__ . '/../../templates/footer.php'; ?>
