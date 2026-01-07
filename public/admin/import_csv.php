<?php
session_start();
require_once __DIR__ . '/../../app/helpers/auth.php';
require_once __DIR__ . '/../../app/helpers/csrf.php';
require_once __DIR__ . '/../../app/models/AdminModel.php';

require_any_role(['admin', 'sekretariat']);
$message = '';
$preview = $_SESSION['csv_preview'] ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? 'upload';

    if ($action === 'upload') {
        if (!isset($_FILES['csv']) || $_FILES['csv']['error'] !== UPLOAD_ERR_OK) {
            $message = 'Nie udało się wczytać pliku.';
        } else {
            $handle = fopen($_FILES['csv']['tmp_name'], 'r');
            $header = fgetcsv($handle, 0, ';');
            $rows = [];

            while (($row = fgetcsv($handle, 0, ';')) !== false) {
                $data = array_combine($header, $row);
                if ($data) {
                    $rows[] = $data;
                }
            }
            fclose($handle);
            $_SESSION['csv_preview'] = $rows;
            $preview = $rows;
            $message = 'Podgląd pliku wczytany. Sprawdź dane i zatwierdź import.';
        }
    }

    if ($action === 'import') {
        $imported = 0;
        foreach ($preview as $data) {
            $login = trim($data['login'] ?? '');
            $haslo = trim($data['haslo'] ?? '');
            $imie = trim($data['imie'] ?? '');
            $nazwisko = trim($data['nazwisko'] ?? '');
            $email = trim($data['email'] ?? '');
            $rola = trim($data['rola'] ?? '');
            $telefon = trim($data['telefon'] ?? '');
            $klasa = (int) ($data['id_klasy'] ?? 0);
            $nrDziennika = (int) ($data['nr_dziennika'] ?? 0);

            if ($login === '' || $haslo === '' || $email === '' || $rola === '') {
                continue;
            }

            $exists = $pdo->prepare('SELECT COUNT(*) FROM uzytkownik WHERE login = :login OR email = :email');
            $exists->execute([':login' => $login, ':email' => $email]);
            if ($exists->fetchColumn() > 0) {
                continue;
            }

            $userId = AdminModel::addUser($pdo, [
                ':login' => $login,
                ':haslo' => password_hash($haslo, PASSWORD_DEFAULT),
                ':imie' => $imie,
                ':nazwisko' => $nazwisko,
                ':email' => $email,
                ':rola' => $rola,
                ':telefon' => $telefon ?: null,
            ]);

            if ($rola === 'nauczyciel') {
                AdminModel::addTeacher($pdo, $userId);
            } elseif ($rola === 'rodzic') {
                AdminModel::addParent($pdo, $userId);
            } elseif ($rola === 'uczen' && $klasa > 0 && $nrDziennika > 0) {
                AdminModel::addStudent($pdo, $userId, $klasa, $nrDziennika);
            }
            $imported++;
        }
        unset($_SESSION['csv_preview']);
        $preview = [];
        $message = 'Zaimportowano: ' . $imported . ' rekordów.';
    }
}

$title = 'Import CSV';
require __DIR__ . '/../../templates/header.php';
?>
<div class="card">
    <h2>Import CSV</h2>
    <?php if ($message): ?>
        <p class="success"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>
    <p>Wymagany separator: <strong>;</strong>. Kolumny: login;haslo;imie;nazwisko;email;rola;telefon;id_klasy;nr_dziennika</p>
    <form method="post" enctype="multipart/form-data">
        <?php echo csrf_input(); ?>
        <input type="hidden" name="action" value="upload">
        <div class="field">
            <label for="csv">Plik CSV</label>
            <input type="file" name="csv" id="csv" accept=".csv" required>
        </div>
        <button class="btn" type="submit">Wczytaj podgląd</button>
    </form>
</div>

<?php if ($preview): ?>
    <div class="card">
        <h3>Podgląd danych</h3>
        <table>
            <thead>
                <tr>
                    <?php foreach (array_keys($preview[0]) as $column): ?>
                        <th><?php echo htmlspecialchars($column, ENT_QUOTES, 'UTF-8'); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($preview as $row): ?>
                    <tr>
                        <?php foreach ($row as $value): ?>
                            <td><?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <form method="post" style="margin-top: 16px;">
            <?php echo csrf_input(); ?>
            <input type="hidden" name="action" value="import">
            <button class="btn" type="submit">Zatwierdź import</button>
        </form>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/../../templates/footer.php'; ?>
