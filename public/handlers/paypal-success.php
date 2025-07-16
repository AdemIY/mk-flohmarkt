<?php
require_once __DIR__ . '/../../includes/db.php';
session_start();

$bookingId = intval($_GET['booking_id'] ?? 0);

if ($bookingId <= 0) {
    http_response_code(400);
    exit('Ungültige Buchungs-ID.');
}

try {
    $db = connect();

    // Buchung prüfen
    $stmt = $db->prepare("
        SELECT b.*, u.first_name, u.last_name, u.email, s.label AS stand_type, s.price AS stand_type_price, e.event_date, e.location
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN stand_types s ON b.stand_type_id = s.id
        JOIN events e ON b.event_id = e.id
        WHERE b.id = :id
    ");
    $stmt->execute(['id' => $bookingId]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        throw new Exception("Buchung nicht gefunden.");
    }

    if ($booking['is_paid']) {
        // Buchung wurde bereits bezahlt – Session nur setzen, nicht nochmal aktualisieren
        $_SESSION['booking_success'] = [
            'first_name' => $booking['first_name'],
            'last_name' => $booking['last_name'],
            'email' => $booking['email'],
            'stand_type' => $booking['stand_type'],
            'stand_type_price' => $booking['stand_type_price'],
            'event_date' => $booking['event_date'],
            'location' => $booking['location'],
            'message' => $booking['message']
        ];

        header("Location: /booking-feedback.php");
        exit;
    }

    // Buchung als bezahlt markieren
    $stmt = $db->prepare("UPDATE bookings SET is_paid = 1 WHERE id = :id");
    $stmt->execute(['id' => $bookingId]);

    $_SESSION['booking_success'] = [
        'first_name' => $booking['first_name'],
        'last_name' => $booking['last_name'],
        'email' => $booking['email'],
        'stand_type' => $booking['stand_type'],
        'stand_type_price' => $booking['stand_type_price'],
        'event_date' => $booking['event_date'],
        'location' => $booking['location'],
        'message' => $booking['message']
    ];

    header("Location: /booking-feedback.php");
    exit;

} catch (Exception $e) {
    http_response_code(500);
    exit('Fehler: ' . $e->getMessage());
}