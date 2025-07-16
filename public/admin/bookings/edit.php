<?php
// admin/bookings/edit.php

require_once __DIR__ . '/../../../includes/auth.php';
requireAdmin();  // session_start(), Login- und Admin-Check übernehmen

require_once __DIR__ . '/../../../includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: /admin/bookings/administer-bookings.php');
    exit;
}

try {
    $db = connect();

    // 1) Wenn das Formular abgeschickt wurde, speichern:
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // 1a) Zuerst laden wir die user_id zur Buchung
        $stmt = $db->prepare("SELECT user_id FROM bookings WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            header('Location: /admin/bookings/administer-bookings.php');
            exit;
        }
        $userId = $row['user_id'];

        // 1b) Users-Tabelle updaten
        $stmtU = $db->prepare("
            UPDATE users
            SET first_name = :fn,
                last_name  = :ln,
                email      = :email
            WHERE id = :uid
        ");
        $stmtU->execute([
            'fn' => $_POST['first_name'],
            'ln' => $_POST['last_name'],
            'email' => $_POST['email'],
            'uid' => $userId,
        ]);

        // 1c) Nur die Nachricht in bookings updaten
        $stmtB = $db->prepare("
            UPDATE bookings
            SET message = :msg
            WHERE id = :id
        ");
        $stmtB->execute([
            'msg' => $_POST['message'],
            'id' => $id,
        ]);

        header('Location: /admin/bookings/administer-bookings.php');
        exit;
    }

    // 2) Wenn nicht POST: Daten für das Formular laden
    $stmt = $db->prepare("
        SELECT 
            b.id           AS booking_id,
            b.user_id      AS user_id,
            u.first_name,
            u.last_name,
            u.email,
            b.message
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        WHERE b.id = :id
    ");
    $stmt->execute(['id' => $id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        header('Location: /admin/bookings/administer-bookings.php');
        exit;
    }

} catch (PDOException $e) {
    die("Datenbank-Fehler: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}
?>
<?php include __DIR__ . '/../../partials/header.php'; ?>

<main class="py-32 px-4 max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Buchung #<?= htmlspecialchars($booking['booking_id']) ?> bearbeiten</h1>
    <form method="post" class="bg-white p-6 rounded-lg shadow space-y-4">
        <div>
            <label class="block text-sm font-medium">Vorname</label>
            <input
                    name="first_name"
                    type="text"
                    value="<?= htmlspecialchars($booking['first_name'], ENT_QUOTES, 'UTF-8') ?>"
                    class="w-full border rounded p-2"
                    required
            >
        </div>
        <div>
            <label class="block text-sm font-medium">Nachname</label>
            <input
                    name="last_name"
                    type="text"
                    value="<?= htmlspecialchars($booking['last_name'], ENT_QUOTES, 'UTF-8') ?>"
                    class="w-full border rounded p-2"
                    required
            >
        </div>
        <div>
            <label class="block text-sm font-medium">E-Mail</label>
            <input
                    name="email"
                    type="email"
                    value="<?= htmlspecialchars($booking['email'], ENT_QUOTES, 'UTF-8') ?>"
                    class="w-full border rounded p-2"
                    required
            >
        </div>
        <div>
            <label class="block text-sm font-medium">Nachricht</label>
            <textarea
                    name="message"
                    class="w-full border rounded p-2"
                    rows="4"
            ><?= htmlspecialchars($booking['message'], ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>
        <div class="text-right space-x-2">
            <a
                    href="/admin/bookings/administer-bookings.php"
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

<?php include __DIR__ . '/../../partials/footer.php'; ?>
