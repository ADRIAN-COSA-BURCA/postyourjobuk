<?php
$pdo = new PDO('mysql:host=localhost;dbname=postyourjobhere', 'root', 'YOUR_PASSWORD_HERE');
$hash = password_hash('password', PASSWORD_DEFAULT);
$stmt = $pdo->prepare("UPDATE tenants SET password_hash = ? WHERE email = 'admin@postyourjobhere.com'");
$stmt->execute([$hash]);
echo "Password updated successfully!";
?>