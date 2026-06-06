<?php
require_once __DIR__ . '/config/database.php';

$db = new Database();
$conn = $db->getConnection();

$hash = password_hash('password123', PASSWORD_BCRYPT);
$hashAdmin = password_hash('admin123', PASSWORD_BCRYPT);

$conn->prepare("UPDATE usuarios SET password = ? WHERE rol = 'USER'")->execute([$hash]);
$conn->prepare("UPDATE usuarios SET password = ? WHERE rol = 'ADMIN'")->execute([$hashAdmin]);

echo "Hecho. Hash user: " . $hash . " | Hash admin: " . $hashAdmin;