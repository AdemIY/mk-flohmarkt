<?php
require_once __DIR__ . '/../../includes/db.php';

header('Content-Type: application/json');
try {
    $db = connect();
    $email = $_GET['email'] ?? '';
    $stmt = $db->prepare("SELECT password, first_name, last_name FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    echo $e->getMessage();
}

if ($user) {
    echo json_encode([
        'exists' => true,
        'has_password' => !empty($user['password']),
        'first_name' => $user['first_name'] ?? '',
        'last_name' => $user['last_name'] ?? ''
    ]);
} else {
    echo json_encode([
        'exists' => false
    ]);
}