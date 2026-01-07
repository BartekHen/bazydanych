<?php
session_start();
require_once __DIR__ . '/../../app/helpers/auth.php';
require_once __DIR__ . '/../../app/helpers/csrf.php';
require_once __DIR__ . '/../../app/models/TeacherModel.php';

require_role('nauczyciel');
$teacherId = TeacherModel::findTeacherId($pdo, current_user()['id_uzytkownika']);
$pwk = isset($_GET['pwk']) ? (int) $_GET['pwk'] : 0;

$stmt = $pdo->prepare('SELECT pwk.id_przedmiot_w_klasie, k.nazwa AS klasa, p.nazwa AS przedmiot
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

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $temat = trim($_POST['temat'] ?? '');
    $data = $_POST['data'] ?? '';
    if ($temat === '' || $data === '') {
        $message = 'Temat i data są wymagane.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO lekcja (temat, data, id_przedmiot_w_klasie) VALUES (:temat, :data, :pwk)');
        $stmt->execute([':temat' => $temat, ':data' => $data, ':pwk' => $pwk]);
        $message = 'Dodano temat lekcji.';
    }
}

$title = 'Temat lekcji';
require __DIR__ . '/../../templates/header.php';
?>
<div class="card">
    <h2>Temat lekcji - <?php echo htmlspecialchars($assignment['klasa'] . ' / ' . $assignment['przedmiot'], ENT_QUOTES, 'UTF-8'); ?></h2>
    <?php if ($message): ?>
        <p class="success"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>
    <form method="post">
        <?php echo csrf_input(); ?>
        <div class="field">
            <label for="temat">Temat</label>
            <input type="text" name="temat" id="temat" required>
        </div>
        <div class="field">
            <label for="data">Data</label>
            <input type="date" name="data" id="data" required>
        </div>
        <button class="btn" type="submit">Dodaj temat</button>
    </form>
</div>
<?php require __DIR__ . '/../../templates/footer.php'; ?>
