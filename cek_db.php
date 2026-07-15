<?php
$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Password: admin123<br>";
echo "Hash: " . $hash;
?>