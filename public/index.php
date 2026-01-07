<?php
session_start();
require_once __DIR__ . '/../app/helpers/auth.php';

if (current_user()) {
    header('Location: /home.php');
    exit;
}

header('Location: /info.php');
exit;
