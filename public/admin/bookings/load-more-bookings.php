<?php
require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/auth.php';
requireAdmin();
$offset = intval($_GET['offset'] ?? 0);
$limit = 20;

try {
    $db = connect();

    // Schritt 1: Buchungen abrufen
    $stmt = $db->prepare("
        SELECT
            b.id AS booking_id,
            u.first_name, u.last_name, u.email,
            s.label AS stand_type, s.price AS stand_price,
            e.event_date, e.location, e.title AS event_title,
            b.message, b.confirmed_booking, b.created_at, b.is_paid, b.total_amount
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN stand_types s ON b.stand_type_id = s.id
        JOIN events e ON b.event_id = e.id
        ORDER BY b.created_at DESC
        LIMIT $limit OFFSET $offset
    ");
    $stmt->execute();
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Schritt 2: Optionale Zusatzoptionen abrufen
    $bookingIds = array_column($bookings, 'booking_id');

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
        foreach ($bookings as &$booking) {
            $booking['options'] = $optionsByBooking[$booking['booking_id']] ?? [];
        }
        unset($booking);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}

header('Content-Type: application/json');
echo json_encode($bookings);
