<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../app/helpers/auth.php';
require_once __DIR__ . '/../app/helpers/csrf.php';

$user = current_user();
$role = current_role();
$title = $title ?? 'Dziennik szkolny';
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 0; color: #222; }
        header { background: #1f4e79; color: #fff; padding: 16px 24px; }
        header .brand { font-weight: bold; font-size: 20px; }
        nav a { color: #fff; margin-right: 16px; text-decoration: none; font-weight: 600; }
        .container { max-width: 1100px; margin: 24px auto; padding: 0 16px; }
        .card { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 3px 8px rgba(0,0,0,0.08); margin-bottom: 16px; }
        .grid { display: grid; gap: 16px; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); }
        .tile { background: #fff; padding: 18px; border-radius: 10px; text-decoration: none; color: #222; border-left: 6px solid #1f4e79; }
        .tile strong { display: block; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; background: #fff; }
        th, td { padding: 10px; border-bottom: 1px solid #e0e0e0; text-align: left; }
        th { background: #f0f3f8; }
        .btn { display: inline-block; padding: 8px 14px; background: #1f4e79; color: #fff; border-radius: 6px; text-decoration: none; border: none; cursor: pointer; }
        .btn-secondary { background: #6c757d; }
        .btn-danger { background: #c0392b; }
        .error { color: #c0392b; font-weight: bold; }
        .success { color: #2e7d32; font-weight: bold; }
        form .field { margin-bottom: 12px; }
        label { display: block; font-weight: 600; margin-bottom: 4px; }
        input[type="text"], input[type="password"], input[type="date"], input[type="time"], input[type="number"], select { width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #ccc; }
    </style>
</head>
<body>
    <header>
        <div class="brand">Dziennik szkolny</div>
        <nav>
            <a href="/info.php">Informacje</a>
            <?php if ($user): ?>
                <a href="/home.php">Start</a>
                <?php if (in_array($role, ['uczen', 'rodzic'], true)): ?>
                    <a href="/dziennik/index.php">Dziennik</a>
                    <a href="/sprawdziany/index.php">Sprawdziany</a>
                <?php endif; ?>
                <?php if ($role === 'nauczyciel'): ?>
                    <a href="/nauczyciel/index.php">Nauczyciel</a>
                    <a href="/sprawdziany/index.php">Sprawdziany</a>
                <?php endif; ?>
                <?php if (in_array($role, ['admin', 'sekretariat'], true)): ?>
                    <a href="/admin/index.php">Administracja</a>
                <?php endif; ?>
                <a href="/logout.php">Wyloguj</a>
            <?php else: ?>
                <a href="/login.php">Logowanie</a>
            <?php endif; ?>
        </nav>
    </header>
    <div class="container">
