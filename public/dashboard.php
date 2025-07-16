<?php
// Datei: public/dashboard.php

require_once __DIR__ . '/../includes/auth.php';
requireLogin();  // Session starten + Redirect, falls nicht eingeloggt

require_once __DIR__ . '/../includes/db.php';

$userId = $_SESSION['user_id'];
$isAdmin = isAdmin();

// Datum-Formatter
$formatter = new IntlDateFormatter(
    'de_DE',
    IntlDateFormatter::FULL,
    IntlDateFormatter::NONE,
    'Europe/Berlin',
    IntlDateFormatter::GREGORIAN,
    'EEEE, d. MMMM yyyy'
);

try {
    $db = connect();

    // 1) Alle Buchungen des Users laden
    $stmt = $db->prepare("
        SELECT 
            b.id            AS booking_id,
            s.label         AS stand_type,
            s.price         AS stand_price,
            e.event_date,
            e.location,
            e.title         AS event_title,
            b.message,
            b.created_at,
            b.customer_type,
            b.total_amount
        FROM bookings b
        JOIN stand_types s ON b.stand_type_id = s.id
        JOIN events e       ON b.event_id = e.id
        WHERE b.user_id = :user_id
        ORDER BY e.event_date DESC, b.created_at DESC
    ");
    $stmt->execute(['user_id' => $userId]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2) Zusatz-Optionen pro Buchung holen (N+1-Query)
    foreach ($bookings as &$booking) {
        $optStmt = $db->prepare("
            SELECT bo.label, bo.price
            FROM booking_option_selection bos
            JOIN booking_options bo ON bo.id = bos.option_id
            WHERE bos.booking_id = :booking_id
        ");
        $optStmt->execute(['booking_id' => $booking['booking_id']]);
        $booking['options'] = $optStmt->fetchAll(PDO::FETCH_ASSOC);
    }
    unset($booking);

    // 3) User-Daten laden
    $stmtUser = $db->prepare("SELECT * FROM users WHERE id = :id");
    $stmtUser->execute(['id' => $userId]);
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Fehler beim Laden deiner Daten: " . $e->getMessage());
}
?>
<?php include __DIR__ . '/partials/header.php'; ?>

<h1 class="text-3xl font-bold text-center mb-10 max-w-5xl mx-auto px-4 pt-32">
    Willkommen in deinem <span class="text-primary">Dashboard</span>,
    <span class="text-primary"><?= htmlspecialchars($user['first_name']) ?></span>!
</h1>

<main class="xl:grid xl:grid-cols-12 max-w-6xl mx-auto px-4 mb-10 gap-10 space-y-6">
    <?php if (isset($_GET['updated']) && $_GET['updated'] == 1): ?>
        <div id="success-message"
             class="bg-green-100 text-green-800 border border-green-300 rounded p-4 mb-6 col-span-12 relative">
            ✅ Persönliche Daten erfolgreich aktualisiert!
            <button
                    onclick="document.getElementById('success-message').remove()"
                    class="absolute top-1 right-1 text-gray-600 bg-gray-300/40 hover:bg-gray-400/60 rounded-full px-2 py-1 text-sm leading-none font-bold"
                    aria-label="Schließen"
                    title="Schließen"
            >×
            </button>
        </div>

    <?php endif; ?>
    <!-- Profil-Daten -->
    <section class="xl:col-span-5">
        <h3 class="text-2xl mb-4 font-bold">Deine <span class="text-primary">Daten</span></h3>
        <div class="bg-white p-6 rounded-2xl shadow-lg space-y-6 relative">
            <a href="user/edit-profile.php?id=<?= $user['id'] ?>" aria-label="Buchung bearbeiten"
               class="px-4 py-2 bg-yellow-200 rounded hover:bg-yellow-300 text-sm absolute right-5 top-5">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                     fill="none">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                          d="M20.8477 1.87868C19.6761 0.707109 17.7766 0.707105 16.605 1.87868L2.44744 16.0363C2.02864 16.4551 1.74317 16.9885 1.62702 17.5692L1.03995 20.5046C0.760062 21.904 1.9939 23.1379 3.39334 22.858L6.32868 22.2709C6.90945 22.1548 7.44285 21.8693 7.86165 21.4505L22.0192 7.29289C23.1908 6.12132 23.1908 4.22183 22.0192 3.05025L20.8477 1.87868ZM18.0192 3.29289C18.4098 2.90237 19.0429 2.90237 19.4335 3.29289L20.605 4.46447C20.9956 4.85499 20.9956 5.48815 20.605 5.87868L17.9334 8.55027L15.3477 5.96448L18.0192 3.29289ZM13.9334 7.3787L3.86165 17.4505C3.72205 17.5901 3.6269 17.7679 3.58818 17.9615L3.00111 20.8968L5.93645 20.3097C6.13004 20.271 6.30784 20.1759 6.44744 20.0363L16.5192 9.96448L13.9334 7.3787Z"
                          fill="#0F0F0F"/>
                </svg>
            </a>
            <?php if ($isAdmin): ?>
                <div class="flex items-center gap-3">
                    <img src="/img/crown-gif2.gif" alt="Admin Krone" width="32" height="32">
                    <span class="text-primary font-semibold text-lg">
                            <?= ucfirst(htmlspecialchars($user['role'])) ?>
                        </span>
                </div>
            <?php endif; ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-gray-900">
                <div>
                    <p class="text-sm font-medium text-primary">Vorname</p>
                    <p><?= htmlspecialchars($user['first_name']) ?></p>
                </div>
                <div>
                    <p class="text-sm font-medium text-primary">Nachname</p>
                    <p><?= htmlspecialchars($user['last_name']) ?></p>
                </div>
                <div>
                    <p class="text-sm font-medium text-primary">E-Mail</p>
                    <p class="break-words"><?= htmlspecialchars($user['email']) ?></p>
                </div>
                <div>
                    <p class="text-sm font-medium text-primary">Registriert am</p>
                    <p><?= (new DateTime($user['created_at']))->format('d.m.Y') ?></p>
                </div>
                <a href="/participation-conditions.php"
                   class="inline-block bg-primary text-white text-center px-6 py-3 rounded-lg font-bold shadow tracking-wider hover:opacity-90 xl:col-span-2">
                    Teilnahmebedingungen ansehen
                </a>
            </div>
        </div>
    </section>

    <!-- Buchungen + Optionen -->
    <section class="xl:col-span-7 space-y-6">
        <?php if (empty($bookings)): ?>
            <p class="text-center text-xl font-semibold">Du hast noch keine Buchungen.</p>
        <?php else: ?>
            <h3 class="text-2xl font-bold">Deine Buchungen</h3>
            <?php foreach ($bookings as $b): ?>
                <div class="bg-white p-5 rounded-xl shadow-md space-y-2 relative">
                    <h4 class="text-lg font-semibold text-primary mb-1">
                        Buchung #<?= htmlspecialchars($b['booking_id']) ?>
                    </h4>
                    <p class="text-sm text-gray-600">
                        <strong>Buchung durchgeführt als:</strong>
                        <?= $b['customer_type'] === 'gewerblich' ? 'Gewerbekunde' : 'Privatkunde' ?>
                    </p>
                    <p class="text-sm text-gray-600">
                        <strong>Event:</strong> <?= htmlspecialchars($b['event_title']) ?>
                    </p>
                    <p class="text-sm text-gray-600">
                        <strong>Datum:</strong>
                        <?= $formatter->format(new DateTime($b['event_date'])) ?>
                    </p>
                    <p class="text-sm text-gray-600">
                        <strong>Ort:</strong> <?= htmlspecialchars($b['location']) ?>
                    </p>
                    <p class="text-sm text-gray-600">
                        <strong>Stand:</strong>
                        <?= htmlspecialchars($b['stand_type']) ?> –
                        <?= number_format($b['stand_price'], 2, ',', '.') ?> €
                    </p>
                    <?php if (!empty($b['message'])): ?>
                        <p class="text-sm text-gray-700">
                            <strong>Nachricht:</strong>
                            <?= nl2br(htmlspecialchars($b['message'])) ?>
                        </p>
                    <?php endif; ?>
                    <?php if (!empty($b['options'])): ?>
                        <div class="mt-2">
                            <p class="text-sm font-medium text-gray-700 mb-1">➕ Zusatzoptionen:</p>
                            <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
                                <?php foreach ($b['options'] as $opt): ?>
                                    <li>
                                        <?= htmlspecialchars($opt['label']) ?>
                                        <?= $opt['price'] > 0
                                            ? '(+' . number_format($opt['price'], 2, ',', '.') . ' €)'
                                            : '(kostenlos)' ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <p class="text-sm text-gray-700 font-semibold mt-2">
                        💶 Gesamtsumme: <?= number_format((float)$b['total_amount'], 2, ',', '.') ?> €
                    </p>

                    <p class="text-xs text-gray-500 mt-2">
                        🕒 Gebucht am <?= (new DateTime($b['created_at']))->format('d.m.Y H:i') ?>
                    </p>
                    <a href="user/edit-booking.php?id=<?= $b['booking_id'] ?>" aria-label="Buchung bearbeiten"
                       class="px-4 py-2 bg-yellow-200 rounded hover:bg-yellow-300 text-sm absolute right-5 top-5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                             fill="none">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                  d="M20.8477 1.87868C19.6761 0.707109 17.7766 0.707105 16.605 1.87868L2.44744 16.0363C2.02864 16.4551 1.74317 16.9885 1.62702 17.5692L1.03995 20.5046C0.760062 21.904 1.9939 23.1379 3.39334 22.858L6.32868 22.2709C6.90945 22.1548 7.44285 21.8693 7.86165 21.4505L22.0192 7.29289C23.1908 6.12132 23.1908 4.22183 22.0192 3.05025L20.8477 1.87868ZM18.0192 3.29289C18.4098 2.90237 19.0429 2.90237 19.4335 3.29289L20.605 4.46447C20.9956 4.85499 20.9956 5.48815 20.605 5.87868L17.9334 8.55027L15.3477 5.96448L18.0192 3.29289ZM13.9334 7.3787L3.86165 17.4505C3.72205 17.5901 3.6269 17.7679 3.58818 17.9615L3.00111 20.8968L5.93645 20.3097C6.13004 20.271 6.30784 20.1759 6.44744 20.0363L16.5192 9.96448L13.9334 7.3787Z"
                                  fill="#0F0F0F"/>
                        </svg>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <!-- Admin-Navi -->
    <?php if ($isAdmin): ?>
        <?php include __DIR__ . '/admin/sections/admin-navigation.php'; ?>
    <?php endif; ?>

</main>

<?php include __DIR__ . '/partials/footer.php'; ?>
