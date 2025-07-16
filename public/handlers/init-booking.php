<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
session_start();
header('Content-Type: application/json');

try {
    $db = connect();

    // Eingaben aus Formular
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $standTypeId = intval($_POST['stand_type_id'] ?? 0);
    $eventId = intval($_POST['event_id'] ?? 0);
    $message = trim($_POST['message'] ?? '');
    $isActive = intval($_POST['is_active'] ?? 1);
    $confirmedBooking = isset($_POST['confirmed_booking']) ? 1 : 0;
    $customerType = $_POST['customer_type'] ?? 'privat';

    // Validierung
    $allowedTypes = ['privat', 'gewerblich'];
    if (!in_array($customerType, $allowedTypes, true)) {
        http_response_code(400);
        exit('Ungültiger Wert für customer_type');
    }
    if (!$confirmedBooking) {
        throw new Exception("Bitte bestätige die Teilnahmebedingungen.");
    }
    if (!$firstName || !$lastName || !$email || $standTypeId <= 0 || $eventId <= 0) {
        throw new Exception("Bitte fülle alle Pflichtfelder aus.");
    }

    // 1. Standplatz-Typ laden
    $stmt = $db->prepare("SELECT id, label, price FROM stand_types WHERE id = :id");
    $stmt->execute(['id' => $standTypeId]);
    $stand = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$stand) {
        throw new Exception("Ungültiger Standplatz-Typ.");
    }
    $amount = floatval($stand['price']);

    // 2. Event-Daten laden
    $stmt = $db->prepare("SELECT event_date, location FROM events WHERE id = :id");
    $stmt->execute(['id' => $eventId]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$event) {
        throw new Exception("Ungültige Veranstaltung.");
    }

    // 3. Nutzer prüfen oder neu anlegen
    $stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $userId = $user['id'];
    } else {
        $stmt = $db->prepare("INSERT INTO users (first_name, last_name, email, is_active) VALUES (:first_name, :last_name, :email, :is_active)");
        $stmt->execute([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'is_active' => $isActive
        ]);
        $userId = $db->lastInsertId();
    }

    // 4. Duplikatprüfung
    $stmt = $db->prepare("
        SELECT b.id, u.first_name, u.last_name, u.email,
               s.label AS stand_type, s.price AS stand_type_price,
               e.event_date, e.location, b.message,
               b.customer_type, b.confirmed_booking
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN stand_types s ON b.stand_type_id = s.id
        JOIN events e ON b.event_id = e.id
        WHERE b.user_id = :user_id AND b.stand_type_id = :stand_type_id AND b.event_id = :event_id
    ");
    $stmt->execute([
        'user_id' => $userId,
        'stand_type_id' => $standTypeId,
        'event_id' => $eventId
    ]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        // Zusatzoptionen laden
        $stmt = $db->prepare("
            SELECT option_id FROM booking_option_selection WHERE booking_id = :booking_id
        ");
        $stmt->execute(['booking_id' => $existing['id']]);
        $optionRows = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $existing['booking_options'] = $optionRows;

        // Standprice laden und brechnen
        $standPrice = (float)$existing['stand_type_price'];
        $optionIds = $optionRows;

        $existing['total_amount'] = calculateTotalAmount($standPrice, $optionIds, $db);
        $_SESSION['already_booked'] = $existing;

        // Session setzen
        $_SESSION['already_booked'] = $existing;

        echo json_encode(['success' => false, 'redirect' => '/booking-feedback.php']);
        exit;
    }
    $selectedOptions = $_POST['booking_options'] ?? [];
    // 4b. Zusatzoptionen-Preise laden & Gesamtsumme berechnen
    $totalAmount = $amount; // Startwert = Standplatzpreis

    $totalAmount = calculateTotalAmount($amount, $selectedOptions, $db);


    // 5. Buchung anlegen (noch unbezahlt)
    $stmt = $db->prepare("
        INSERT INTO bookings (user_id, stand_type_id, event_id, message, confirmed_booking, customer_type, is_paid, total_amount, created_at)
        VALUES (:user_id, :stand_type_id, :event_id, :message, :confirmed_booking, :customer_type, 0, :total_amount, NOW())
    ");
    $stmt->execute([
        'user_id' => $userId,
        'stand_type_id' => $standTypeId,
        'event_id' => $eventId,
        'message' => $message,
        'confirmed_booking' => $confirmedBooking,
        'customer_type' => $customerType,
        'total_amount' => $totalAmount
    ]);
    $bookingId = $db->lastInsertId();

    // 5b. Zusatzoptionen einfügen
    $selectedOptions = $_POST['booking_options'] ?? [];

    if (is_array($selectedOptions) && count($selectedOptions) > 0) {
        $stmt = $db->prepare("INSERT INTO booking_option_selection (booking_id, option_id) VALUES (:booking_id, :option_id)");
        foreach ($selectedOptions as $optionId) {
            $stmt->execute([
                'booking_id' => $bookingId,
                'option_id' => (int)$optionId
            ]);
        }
    }

    // 6. Session für Erfolg vorbereiten
    $_SESSION['booking_success'] = [
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => $email,
        'stand_type' => $stand['label'],
        'stand_type_price' => $amount,
        'event_date' => $event['event_date'],
        'location' => $event['location'],
        'message' => $message,
        'booking_options' => $selectedOptions,
        'confirmed_booking' => $confirmedBooking,
        'customer_type' => $customerType,
        'total_amount' => $totalAmount
    ];

    echo json_encode([
        'success' => true,
        'booking_id' => $bookingId,
        'amount' => number_format($totalAmount, 2, '.', '')
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}