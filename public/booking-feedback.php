<?php
session_start();

$data = $_SESSION['booking_success'] ?? $_SESSION['already_booked'] ?? null;
$isDuplicate = isset($_SESSION['already_booked']);
$selectedOptionIds = $data['booking_options'] ?? [];
$confirmedBooking = isset($data['confirmed_booking']);

if (!$data) {
    header("Location: /");
    exit;
}

unset($_SESSION['booking_success'], $_SESSION['already_booked']);

$selectedOptions = [];
if (is_array($selectedOptionIds) && count($selectedOptionIds) > 0) {
    require_once __DIR__ . '/../includes/db.php';
    $db = connect();

    $placeholders = implode(',', array_fill(0, count($selectedOptionIds), '?'));
    $stmt = $db->prepare("SELECT label, price FROM booking_options WHERE id IN ($placeholders)");
    $stmt->execute($selectedOptionIds);
    $selectedOptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$formatter = new IntlDateFormatter(
    'de_DE',
    IntlDateFormatter::FULL,
    IntlDateFormatter::NONE,
    'Europe/Berlin',
    IntlDateFormatter::GREGORIAN,
    'EEEE, d. MMMM yyyy'
);
$formattedDate = $formatter->format(new DateTime($data['event_date']));
?>

<?php include 'partials/header.php'; ?>

<section class="py-32 bg-white">
    <div class="max-w-3xl mx-auto px-6 text-gray-800">
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full mb-6
                <?= $isDuplicate ? 'bg-yellow-100 text-yellow-600' : 'bg-green-100 text-green-600' ?>">
                <?= $isDuplicate ? '⚠️' : '✅' ?>
            </div>
            <h1 class="text-4xl font-bold <?= $isDuplicate ? 'text-yellow-600' : 'text-green-600' ?>">
                <?= $isDuplicate ? 'Bereits gebucht' : 'Buchung erfolgreich!' ?>
            </h1>
            <p class="mt-2 text-lg text-gray-600">
                <?= $isDuplicate
                    ? 'Du hast für diesen Termin bereits einen Standplatz gebucht.'
                    : 'Vielen Dank für deine Buchung! Hier ist deine Zusammenfassung:' ?>
            </p>
        </div>

        <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 space-y-6 shadow">
            <div>
                <h2 class="text-xl font-semibold mb-2">🧍 Persönliche Daten</h2>
                <p>
                    <?php if (isset($data['customer_type']) && $data['customer_type'] === 'gewerblich'): ?>
                        Du hast als Gewerbekunde gebucht.
                    <?php elseif (isset($data['customer_type']) && $data['customer_type'] === 'privat'): ?>
                        Du hast als Privatkunde gebucht.
                    <?php else: ?>
                        Kundentyp nicht bekannt.
                    <?php endif; ?>
                </p>
                <p>
                    <strong>Name:</strong> <?= htmlspecialchars($data['first_name']) ?> <?= htmlspecialchars($data['last_name']) ?>
                </p>
                <p><strong>E-Mail:</strong> <?= htmlspecialchars($data['email']) ?></p>
            </div>

            <div>
                <h2 class="text-xl font-semibold mb-2">🛒 Buchungsdetails</h2>
                <p><strong>Standplatz:</strong> <?= htmlspecialchars($data['stand_type']) ?></p>
                <?php if (isset($data['stand_type_price'])): ?>
                    <p><strong>Preis:</strong> <?= number_format($data['stand_type_price'], 2, ',', '.') ?> €</p>
                <?php endif; ?>
                <p><strong>Veranstaltung:</strong> <?= $formattedDate ?> – <?= htmlspecialchars($data['location']) ?>
                </p>
                <?php if (!empty($data['message'])): ?>
                    <p><strong>Nachricht:</strong> <?= nl2br(htmlspecialchars($data['message'])) ?></p>
                <?php endif; ?>
            </div>

            <?php if (!empty($selectedOptions)): ?>
                <div>
                    <h2 class="text-xl font-semibold mb-2">✨ Zusatzoptionen</h2>
                    <ul class="list-disc list-inside text-gray-700">
                        <?php foreach ($selectedOptions as $option): ?>
                            <li>
                                <?= htmlspecialchars($option['label']) ?>
                                <?php if ($option['price'] > 0): ?>
                                    (+<?= number_format($option['price'], 2, ',', '.') ?> €)
                                <?php else: ?>
                                    (kostenlos)
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="pt-6 mt-6 border-t border-gray-300">
                <h2 class="text-xl font-semibold mb-2">💶 Gesamtsumme</h2>
                <p class="text-lg font-bold text-gray-900">
                    Betrag: <?= number_format((float)$data['total_amount'], 2, ',', '.') ?> €
                </p>
            </div>
        </div>

        <?php if (!$isDuplicate): ?>
            <div class="mt-8 text-center text-gray-600">
                <p>Du erhältst in Kürze eine Bestätigungs-E-Mail (sofern der E-Mail-Versand eingerichtet ist).</p>
            </div>
        <?php endif; ?>

        <?php if (isset($confirmedBooking)): ?>
            <div class="mt-10 text-center">
                Du hast die Teilnahmebedinungen akzeptiert!
                <a href="/participation-conditions.php"
                   class="inline-block bg-primary text-white px-6 py-3 rounded-lg shadow hover:opacity-90">
                    Teilnahmebedingungen ansehen
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'partials/footer.php'; ?>
