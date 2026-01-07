<?php
session_start();
require_once __DIR__ . '/../../app/helpers/auth.php';

require_any_role(['admin', 'sekretariat']);
$stmt = $pdo->query('SELECT id_uzytkownika, imie, nazwisko, email, rola FROM uzytkownik WHERE rola IN ("nauczyciel", "sekretariat", "admin") ORDER BY nazwisko');
$employees = $stmt->fetchAll();

$title = 'Pracownicy';
require __DIR__ . '/../../templates/header.php';
?>
<div class="card">
    <h2>Pracownicy</h2>
    <a class="btn" href="/admin/pracownik_dodaj.php">Dodaj pracownika</a>
</div>
<?php if ($employees): ?>
    <table>
        <thead>
            <tr>
                <th>Imię i nazwisko</th>
                <th>Email</th>
                <th>Rola</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($employees as $employee): ?>
                <tr>
                    <td><?php echo htmlspecialchars($employee['imie'] . ' ' . $employee['nazwisko'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($employee['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($employee['rola'], ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <div class="card">Brak pracowników.</div>
<?php endif; ?>
<?php require __DIR__ . '/../../templates/footer.php'; ?>
