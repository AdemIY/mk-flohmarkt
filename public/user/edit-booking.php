<?php
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();

require_once __DIR__ . '/../../includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: /dashboard.php');
    exit;
}

try {
    $db = connect();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $message = trim($_POST['message'] ?? '');

        $stmt = $db->prepare('UPDATE bookings SET message = :message WHERE id = :id');
        $stmt->execute([
            'message' => $message,
            'id' => $id
        ]);

        // Optional: Weiterleitung oder Erfolgsmeldung
        header("Location: /dashboard.php?updated=1");
        exit;
    }

    $stmt = $db->prepare('SELECT message FROM bookings WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $booking = $stmt->fetch();

} catch (PDOException $e) {
    die("Datenbank-Fehler: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}
?>
<?php include __DIR__ . '/../partials/header.php'; ?>

<main class="py-32 px-4 max-w-lg mx-auto">
    <h1 class="text-2xl font-bold mb-6">Buchung #<?= htmlspecialchars($id) ?> bearbeiten</h1>
    <form method="post" class="bg-white p-6 rounded-lg shadow space-y-4">
        <div>
            <label class="block text-sm font-medium">Nachricht</label>
            <textarea placeholder="Neben wem willst du sitzen? Hast du extra wünsche?"
                      name="message"
                      class="w-full border rounded p-2 focus:outline-none focus:ring-2 focus:ring-primary"
                      rows="4"
            ><?= htmlspecialchars($booking['message'], ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>
        <div class="text-right space-x-2">
            <a
                    href="/dashboard.php"
                    class="inline-block px-4 py-2 border rounded hover:bg-gray-50"
            >Abbrechen</a>
            <button
                    type="submit"
                    class="bg-primary text-white px-4 py-2 rounded hover:opacity-90"
            >Speichern
            </button>
        </div>
    </form>
</main>

<?php include __DIR__ . '/../partials/footer.php'; ?>
