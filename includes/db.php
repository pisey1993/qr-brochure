<?php
$host = 'peoplenpartners.net';
$db   = 'brochure';
$user = 'root';
$pass = 'RootP@ssw0rd';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    $pdo = new PDO($dsn, $user, $pass);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    exit;
}
?>
