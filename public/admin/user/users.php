<?php
require_once __DIR__ . '/../../../includes/db.php';
$config = require __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/functions.php';
requireAdmin();

try {
    $db = connect();
    $stmtUser = $db->prepare("SELECT id, first_name, last_name, email, is_active, role, created_at, newsletter_opt_in FROM users");
    $stmtUser->execute();
    $users = $stmtUser->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log('Fehler bei users.php' . $e->getMessage());
}

?>
<?php foreach ($users as $user) : ?>
    <div><?= htmlspecialchars($user['first_name']) ?></div>
<?php endforeach; ?>