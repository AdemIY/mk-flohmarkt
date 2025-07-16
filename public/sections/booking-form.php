<?php
require_once __DIR__ . '/../../includes/db.php';
require __DIR__ . '/../../includes/functions.php';

try {
    $db = connect();

    // Events holen
    $stmt = $db->prepare("SELECT id, event_date, location, title FROM events WHERE is_active = 1 ORDER BY event_date ASC");
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Standplatz-Typen holen
    $stmt = $db->prepare("SELECT id, label, width_meters, depth_meters, price FROM stand_types WHERE is_active = 1 ORDER BY width_meters, depth_meters");
    $stmt->execute();
    $standTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $db->prepare("SELECT id, label, price FROM booking_options WHERE is_active = 1 ORDER BY id ASC");
    $stmt->execute();
    $bookingOptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formatter für Datum
    $formatter = new IntlDateFormatter(
        'de_DE', IntlDateFormatter::FULL, IntlDateFormatter::NONE,
        'Europe/Berlin', IntlDateFormatter::GREGORIAN,
        'EEEE, d. MMMM yyyy'
    );

} catch (PDOException $e) {
    die("Fehler beim Laden der Buchungsdaten: " . $e->getMessage());
}
?>

<section id="booking" class="bg-white py-32">
    <div class="max-w-2xl mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-bold text-center text-gray-800 mb-8">
            Buchungsformular
        </h2>
        <form id="booking-form" method="POST" class="space-y-6">
            <div class="flex items-center gap-5 mb-4">
                <label class="inline-flex items-center space-x-2 cursor-pointer">
                    <input type="radio" name="customer_type" value="privat"
                           class="h-5 w-5">
                    <span class="text-gray-700">Privat</span>
                </label>

                <label class="inline-flex items-center space-x-2 cursor-pointer">
                    <input type="radio" name="customer_type" value="gewerblich"
                           class="h-5 w-5">
                    <span class="text-gray-700">Gewerblich</span>
                </label>

            </div>
            <div>
                <label for="first_name" class="block font-semibold text-gray-700 mb-1">Vorname</label>
                <input type="text" id="first_name" name="first_name" required placeholder="Vorname"
                       class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            <div>
                <label for="last_name" class="block font-semibold text-gray-700 mb-1">Nachname</label>
                <input type="text" id="last_name" name="last_name" required placeholder="Nachname"
                       class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            <div>
                <label for="email" class="block font-semibold text-gray-700 mb-1">E-Mail-Adresse</label>
                <input type="email" id="email" name="email" required placeholder="deine@email.de"
                       class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            <div>
                <label for="stand_type_id" class="block font-semibold text-gray-700 mb-1">Standplatz-Typ</label>
                <select id="stand_type_id" name="stand_type_id" required
                        class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary">
                    <option value="">Bitte wählen</option>
                    <?php foreach ($standTypes as $type): ?>
                        <option value="<?= $type['id'] ?>" data-price="<?= $type['price'] ?>">
                            <?= htmlspecialchars($type['label']) ?> (<?= $type['width_meters'] ?> m
                            × <?= $type['depth_meters'] ?> m – <?= number_format($type['price'], 2, ',', '.') ?> €)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="event_id" class="block font-semibold text-gray-700 mb-1">Datum wählen</label>
                <select id="event_id" name="event_id" required
                        class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary">
                    <option value="">Bitte wählen</option>
                    <?php foreach ($events as $event): ?>
                        <?php $datum = new DateTime($event['event_date']); ?>
                        <option value="<?= $event['id'] ?>">
                            <?= htmlspecialchars($event['title']) ?> - <?= $formatter->format($datum) ?>
                            – <?= htmlspecialchars($event['location']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="message"
                       class="block font-semibold text-gray-700 mb-1">Nachricht
                    (Anmerkungen)</label>
                <textarea id="message" name="message" rows="4"
                          class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary"
                          placeholder="Was wird verkauft?..."
                          required></textarea>
            </div>

            <!-- Zusatzoptionen Box -->
            <div class="bg-white border border-gray-200 shadow-sm rounded-xl p-6 mb-8">
                <h3 class="text-2xl font-bold mb-4 text-gray-800">Zusatzoptionen</h3>

                <div class="space-y-4 text-gray-800">
                    <?php foreach ($bookingOptions as $option): ?>
                        <label for="option_<?= $option['id'] ?>" class="flex items-start space-x-3 cursor-pointer">
                            <input type="checkbox" name="booking_options[]" value="<?= $option['id'] ?>"
                                   data-price="<?= $option['price'] ?>"
                                   id="option_<?= $option['id'] ?>"
                                   class="mt-1 h-5 w-5 text-primary border-gray-300 rounded focus:ring-primary">
                            <span class="text-lg font-medium">
                    <?= htmlspecialchars($option['label']) ?>
                                <?php if ($option['price'] > 0): ?>
                                    <span class="text-gray-500 font-normal">(+<?= number_format($option['price'], 2, ',', '.') ?> €)</span>
                                <?php else: ?>
                                    <span class="text-gray-500 font-normal">(kostenlos)</span>
                                <?php endif; ?>
                </span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Rechtliches Box -->
            <div class="bg-white border border-gray-200 shadow-sm rounded-xl p-6 mb-8">
                <h3 class="text-2xl font-bold mb-4 text-gray-800">Rechtliches</h3>

                <div class="text-gray-800 space-y-4">
                    <label for="confirmed_booking" class="flex items-start space-x-3 cursor-pointer">
                        <input type="checkbox" name="confirmed_booking" id="confirmed_booking" required
                               class="mt-1 h-5 w-5 text-primary border-gray-300 rounded focus:ring-primary">
                        <span class="text-lg font-medium">
                Ich bestätige verbindlich meine Buchung und habe die
                <a href="/participation-conditions.php" target="_blank" class="text-blue-600 underline font-semibold">
                    Teilnahmebedingungen
                </a> gelesen.
            </span>
                    </label>
                </div>
            </div>

            <!-- Gesamtsumme Box -->
            <div class="bg-white border border-gray-200 shadow-sm rounded-xl p-6 mb-8">
                <h3 class="text-2xl font-bold mb-4 text-gray-800">Gesamtsumme</h3>

                <div class="space-y-4">
                    <div class="flex justify-between text-gray-800">
                        <div class="font-semibold">Standplatz</div>
                        <div id="stand_type_price" class="font-medium text-gray-700">–</div>
                    </div>
                    <div class="flex justify-between text-gray-800">
                        <div class="font-semibold">Zusatzoptionen</div>
                        <div id="booking_options_price" class="font-medium text-gray-700">–</div>
                    </div>
                    <div class="flex justify-between text-primary text-lg font-bold">
                        <div>Gesamtsumme</div>
                        <div id="total-price">–</div>
                    </div>
                </div>
            </div>


            <div class="text-center">
                <button type="button" id="start-payment"
                        class="bg-primary text-white px-6 py-2 rounded hover:opacity-90">
                    Zur Zahlung mit PayPal
                </button>
            </div>
            <div id="paypal-button-container" class="mt-6 hidden"></div>
        </form>
    </div>
</section>

<script src="https://www.paypal.com/sdk/js?client-id=AXrWOhIrLyMcFQf7ldIj0ehNCBWS0fTOiTbCQFFTpt0kanHtxXkG_SwQ8C8SWEuYiTgfa_Ri324iUFSV&currency=EUR"></script>
<script>
    document.getElementById('start-payment').addEventListener('click', function () {
        const form = document.getElementById('booking-form');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const formData = new FormData(form);


        fetch('../handlers/init-booking.php', {
            method: 'POST',
            body: formData
        }).then(res => res.json())
            .then(data => {
                    if (data.success) {
                        document.getElementById('paypal-button-container').classList.remove('hidden');

                        paypal.Buttons({
                            createOrder: function (data2, actions) {
                                return actions.order.create({
                                    purchase_units: [{
                                        amount: {value: data.amount},
                                        reference_id: data.booking_id
                                    }]
                                });
                            },
                            onApprove: function (data2, actions) {
                                return actions.order.capture().then(function (details) {
                                    const transactionId = details.id;


                                    // gesamtes details objekt per fetch an eigene API senden und dort mit PayPal API validieren
                                    fetch('/handlers/paypal-validate.php', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json'
                                        },
                                        body: JSON.stringify(details)
                                    })
                                        .then(response => response.json())
                                        .then(data => {
                                            console.log('Antwort von PHP:', data);
                                        });

                                    window.location.href = "/handlers/paypal-success.php?booking_id=" + data.booking_id;
                                });
                            },
                            onCancel: function () {
                                window.location.href = "/handlers/paypal-cancel.php";
                            }
                        }).render('#paypal-button-container');
                    } else if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        alert("Fehler: " + data.message);
                    }
                }
            )
    });
</script>
<script src="../js/calculatePrice.js"></script>