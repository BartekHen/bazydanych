<?php
session_start();
require_once __DIR__ . '/../../app/helpers/auth.php';
require_once __DIR__ . '/../../app/models/UserModel.php';

require_any_role(['admin', 'sekretariat']);
$query = trim($_GET['q'] ?? '');
$roleFilter = $_GET['rola'] ?? '';
$users = [];

if ($query !== '' || $roleFilter !== '') {
    $users = UserModel::search($pdo, $query, $roleFilter ?: null);
}

$title = 'Użytkownicy';
require __DIR__ . '/../../templates/header.php';
?>
<div class="card">
    <h2>Wyszukiwarka użytkowników</h2>
    <form method="get">
        <div class="field">
            <label for="q">Szukaj (imię/nazwisko/email)</label>
            <input type="text" name="q" id="q" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'UTF-8'); ?>">
        </div>
        <div class="field">
            <label for="rola">Rola</label>
            <select name="rola" id="rola">
                <option value="">-- wszystkie --</option>
                <?php foreach (['uczen', 'rodzic', 'nauczyciel', 'admin', 'sekretariat'] as $rola): ?>
                    <option value="<?php echo $rola; ?>" <?php echo $roleFilter === $rola ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($rola, ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="btn" type="submit">Szukaj</button>
    </form>
</div>

<?php if ($users): ?>
    <table>
        <thead>
            <tr>
                <th>Imię i nazwisko</th>
                <th>Email</th>
                <th>Rola</th>
                <th>Status</th>
                <th>Akcje</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['imie'] . ' ' . $user['nazwisko'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($user['rola'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($user['czy_aktywny'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><a class="btn-secondary btn" href="/admin/uzytkownik_edit.php?id=<?php echo (int) $user['id_uzytkownika']; ?>">Edytuj</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php elseif ($query !== '' || $roleFilter !== ''): ?>
    <div class="card">Brak wyników.</div>
<?php endif; ?>
<?php require __DIR__ . '/../../templates/footer.php'; ?>
