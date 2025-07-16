<?php
require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/auth.php';
requireAdmin();
$isAdmin = isAdmin();
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}
$userId = $_SESSION['user_id'];

// Formatter für Datum
$formatter = new IntlDateFormatter(
    'de_DE', IntlDateFormatter::FULL, IntlDateFormatter::NONE,
    'Europe/Berlin', IntlDateFormatter::GREGORIAN,
    'EEEE, d. MMMM yyyy'
);
try {
    $db = connect();
    $stmt = $db->query("
        SELECT
            b.id AS booking_id,
            u.first_name,
            u.last_name,
            u.email,
            s.label AS stand_type,
            s.price AS stand_price,
            e.event_date,
            e.location,
            e.title AS event_title,
            b.message,
            b.confirmed_booking,
            b.created_at,
            b.is_paid,
            b.total_amount
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN stand_types s ON b.stand_type_id = s.id
        JOIN events e ON b.event_id = e.id
        ORDER BY b.created_at DESC LIMIT 20
    ");
    $allBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $countAllBookings = count($allBookings);
// Zusatzoptionen zu jeder Buchung holen
    $bookingIds = array_column($allBookings, 'booking_id');
    if (count($bookingIds) > 0) {
        $placeholders = implode(',', array_fill(0, count($bookingIds), '?'));
        $stmtOptions = $db->prepare("
        SELECT bos.booking_id, bo.label, bo.price
        FROM booking_option_selection bos
        JOIN booking_options bo ON bos.option_id = bo.id
        WHERE bos.booking_id IN ($placeholders)
    ");
        $stmtOptions->execute($bookingIds);
        $optionsData = $stmtOptions->fetchAll(PDO::FETCH_ASSOC);

        // Gruppieren nach booking_id
        $optionsByBooking = [];
        foreach ($optionsData as $opt) {
            $optionsByBooking[$opt['booking_id']][] = $opt;
        }

        // Optionen zu den Buchungen hinzufügen
        foreach ($allBookings as &$booking) {
            $booking['options'] = $optionsByBooking[$booking['booking_id']] ?? [];
        }
        unset($booking); // Referenz aufheben
    }
    $stmt = $db->prepare("SELECT COUNT(id) AS cnt FROM bookings");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $allBookingCounts = (int)$row['cnt'];


} catch (Exception $e) {
    echo $e->getMessage();
}
?>
<?php if ($isAdmin): ?>
    <?php include '../../partials/header.php'; ?>
    <main class="py-32 px-4 mx-auto max-w-6xl">
        <?php include '../sections/admin-navigation.php'; ?>

        <section class="xl:col-span-12 mt-16">
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <div class="bg-gray-100 py-3 px-6 border-b">
                    <h2 class="text-xl font-semibold text-gray-800">Alle Buchungen | (<span id="countBookings"></span>
                        von <?= $allBookingCounts ?>)</h2>
                </div>
                <ul id="bookingList" class="divide-y divide-gray-200">
                    <?php foreach ($allBookings as $index => $b): ?>
                        <li class="p-6 even:bg-gray-50 ">
                            <div class="grid sm:grid-cols-2 gap-6">
                                <!-- Linke Spalte -->
                                <div class="space-y-1">
                                    <h3 class="text-xl font-bold text-primary mb-1">
                                        Buchung #<?= htmlspecialchars($b['booking_id']) ?>
                                    </h3>
                                    <h4 class="text-xl font-bold text-primary">
                                        <?= htmlspecialchars($b['first_name']) ?> <?= htmlspecialchars($b['last_name']) ?>
                                    </h4>
                                    <p class="text-sm text-gray-500"><?= htmlspecialchars($b['email']) ?></p>

                                    <div class="text-sm text-gray-700 mt-4 space-y-1">
                                        <p><strong>💸 Bezahlt:</strong> <?= $b['is_paid'] === 1 ? 'Ja' : 'Nein' ?></p>
                                        <p><strong>📅 Event:</strong> <?= htmlspecialchars($b['event_title']) ?></p>
                                        <p><strong>📍 Ort:</strong> <?= htmlspecialchars($b['location']) ?></p>
                                        <p><strong>🗓️
                                                Datum:</strong> <?= $formatter->format(new DateTime($b['event_date'])) ?>
                                        </p>
                                        <p><strong>🧱 Standplatz:</strong> <?= htmlspecialchars($b['stand_type']) ?>
                                            – <?= number_format($b['stand_price'], 2, ',', '.') ?> €</p>
                                        <?php if (!empty($b['message'])): ?>
                                            <p><strong>💬
                                                    Nachricht:</strong> <?= nl2br(htmlspecialchars($b['message'])) ?>
                                            </p>
                                            <p><strong>💸
                                                    Gesamtsumme:</strong> <?= number_format(htmlspecialchars($b['total_amount']), 2, ',', '.') ?>
                                                €</p>
                                        <?php endif; ?>
                                        <p class="text-xs text-gray-500 mt-2">
                                            🕒 Gebucht am <?= (new DateTime($b['created_at']))->format('d.m.Y H:i') ?>
                                        </p>
                                    </div>
                                </div>

                                <!-- Rechte Spalte -->
                                <div class="bg-gray-100 p-4 rounded-xl space-y-2">
                                    <p class="text-sm">
                                        <strong>✅
                                            Teilnahmebedingungen:</strong> <?= $b['confirmed_booking'] ? 'Ja' : 'Nein' ?>
                                    </p>

                                    <?php if (!empty($b['options'])): ?>
                                        <div>
                                            <p class="text-sm font-medium text-gray-700 mb-1">➕ Zusatzoptionen:</p>
                                            <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
                                                <?php foreach ($b['options'] as $option): ?>
                                                    <li>
                                                        <?= htmlspecialchars($option['label']) ?>
                                                        <?= $option['price'] > 0 ? '(+' . number_format($option['price'], 2, ',', '.') . ' €)' : '(kostenlos)' ?>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="mt-4 flex space-x-2">
                                    <!-- Bearbeiten -->
                                    <a href="edit.php?id=<?= $b['booking_id'] ?>"
                                       class="px-4 py-2 bg-yellow-200 rounded hover:bg-yellow-300 text-sm">
                                        Bearbeiten
                                    </a>
                                    <!-- Löschen -->
                                    <form action="delete.php" method="post"
                                          onsubmit="return confirm('Buchung wirklich löschen?');">
                                        <input type="hidden" name="id" value="<?= $b['booking_id'] ?>">
                                        <button type="submit"
                                                class="px-4 py-2 bg-red-200 rounded hover:bg-red-300 text-sm">
                                            Löschen
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="text-center mt-6">
                <button id="loadMoreBtn"
                        class="px-4 py-2 bg-primary text-white rounded hover:bg-primary-dark">
                    Weitere 20 Buchungen laden
                </button>
            </div>

        </section>
    </main>
    <script src="/../js/load-more-booking-entries.js"></script>
    <?php include '../../partials/footer.php'; ?>
<?php else: ?>
    <span>Du bist kein Admin bro</span>
<?php endif; ?>