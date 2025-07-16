<?php


$content = json_decode(file_get_contents('php://input'));

$transactionId = $content['transaction_id'] ?? null;
$bookingId = $content['booking_id'] ?? null;


echo file_get_contents('https://armilla.de/');