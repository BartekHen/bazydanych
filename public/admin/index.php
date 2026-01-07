<?php
session_start();
require_once __DIR__ . '/../../app/helpers/auth.php';

require_any_role(['admin', 'sekretariat']);
$title = 'Administracja';
require __DIR__ . '/../../templates/header.php';
?>
<div class="card">
    <h2>Panel administracyjny</h2>
    <p>Wybierz moduł do zarządzania.</p>
</div>
<div class="grid">
    <a class="tile" href="/admin/uzytkownicy.php"><strong>Użytkownicy</strong>Wyszukiwanie i edycja</a>
    <a class="tile" href="/admin/pracownicy.php"><strong>Pracownicy</strong>Lista i dodawanie</a>
    <a class="tile" href="/admin/opieka.php"><strong>Rodzice</strong>Powiązania rodzic-uczeń</a>
    <a class="tile" href="/admin/klasy_przedmioty.php"><strong>Struktura</strong>Klasy, przedmioty, przypisania</a>
    <a class="tile" href="/admin/import_csv.php"><strong>Import CSV</strong>Dodawanie kont</a>
</div>
<?php require __DIR__ . '/../../templates/footer.php'; ?>
