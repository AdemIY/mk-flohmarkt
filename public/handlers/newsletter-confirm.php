<?php
require_once __DIR__ . '/../../includes/db.php';

$error = null;
$success = false;

try {
    $db = connect();
    // 1. Token prüfen
    $token = $_GET['token'] ?? '';

    if (!$token || strlen($token) !== 64) {
        throw new Exception('Ungültiger oder fehlender Token.');
    }

    // 2. Token aus Datenbank holen
    $stmt = $db->prepare("
        SELECT nt.id, nt.user_id, u.email
        FROM newsletter_tokens nt
        JOIN users u ON u.id = nt.user_id
        WHERE nt.token = :token
        AND nt.confirmed_at IS NULL
        AND nt.expires_at >= NOW()
    ");
    $stmt->execute(['token' => $token]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        throw new Exception('Der Bestätigungslink ist ungültig oder abgelaufen.');
    }

    // 3. newsletter_opt_in aktivieren
    $stmt = $db->prepare("UPDATE users SET newsletter_opt_in = 1 WHERE id = :id");
    $stmt->execute(['id' => $row['user_id']]);

    // 4. Token als bestätigt markieren
    $stmt = $db->prepare("UPDATE newsletter_tokens SET confirmed_at = NOW() WHERE id = :id");
    $stmt->execute(['id' => $row['id']]);

    $success = true;

} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<?php include '../partials/header.php'; ?>
    <section class="py-32">
        <?php if ($success): ?>

            <h1 class="text-2xl font-bold text-green-600">Danke für deine Bestätigung!</h1>
            <p class="mt-4 text-gray-700">Du hast den Flohmarkt-Newsletter erfolgreich abonniert.</p>
            <script>
                setTimeout(() => {
                    window.location.href = "/"; // nach Wunsch anpassen
                }, 4000);
            </script>
        <?php else: ?>
            <h1 class="text-2xl font-bold text-red-600">Bestätigung fehlgeschlagen</h1>
            <p class="mt-4 text-gray-700"><?= htmlspecialchars($error) ?></p>
            <p class="mt-2 text-sm text-gray-500">Bitte versuche es erneut oder kontaktiere den Support.</p>
        <?php endif; ?>
    </section>

<?php include '../partials/footer.php'; ?>