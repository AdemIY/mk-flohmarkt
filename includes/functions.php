<?php
function calculateTotalPrice(array $booking, array $options): float
{
    $total = 0;

    $total += $booking['stand_price'] ?? 0;

    foreach ($options as $opt) {
        $total += $opt['price'];
    }

    return $total;
}

// includes/functions.php
function calculateTotalPriceTax(array $booking, array $options): float
{
    $total = 0;

    // Preis für Standplatz
    $total += $booking['stand_price'] ?? 0;

    // Zusatzoptionen summieren
    foreach ($options as $opt) {
        $total += $opt['price'];
    }
    $total *= 0.19;
    return $total;
}

function calculateTotalAmount(float $standPrice, array $optionIds, PDO $db): float
{
    $total = $standPrice;

    if (!empty($optionIds)) {
        $placeholders = implode(',', array_fill(0, count($optionIds), '?'));
        $stmt = $db->prepare("SELECT price FROM booking_options WHERE id IN ($placeholders)");
        $stmt->execute($optionIds);
        $optionPrices = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($optionPrices as $price) {
            $total += (float)$price;
        }
    }

    return $total;
}
