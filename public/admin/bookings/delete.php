<?php
// admin/bookings/delete.php
require_once __DIR__ . '/../../../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../../../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['id'])) {
    $id = (int)$_POST['id'];
    $db = connect();
    $stmt = $db->prepare("DELETE FROM bookings WHERE id = :id");
    $stmt->execute(['id' => $id]);
}

header('Location: administer-bookings.php');
exit;