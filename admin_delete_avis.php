<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "Database.php";


if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin' && isset($_GET['id'])) {
    $db = (new Database())->getConnection();
    $stmt = $db->prepare("DELETE FROM avis WHERE id = ?");
    $stmt->execute([$_GET['id']]);
}
header("Location: admin.php");
exit();