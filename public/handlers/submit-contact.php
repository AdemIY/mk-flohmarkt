<?php
session_start();
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../vendor/autoload.php';
$config = require __DIR__ . '/../../config.php';

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Nur POST erlaubt.');
}
if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
    http_response_code(403);
    exit('Ungültiges CSRF-Token.');
}

$firstName = trim($_POST['firstname'] ?? '');
$lastName = trim($_POST['lastname'] ?? '');
$email = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');
$phone = trim($_POST['phone'] ?? null); // optional
$ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';

if (empty($firstName) || empty($lastName) || empty($email) || empty($message)) {
    http_response_code(400);
    exit('Bitte alle Pflichtfelder ausfüllen.');
}

try {
    $db = connect();

    $stmt = $db->prepare("
        INSERT INTO contact_requests (fist_name, last_name, email, message, phone, ip_address, created_at)
        VALUES (:first_name, :last_name, :email, :message, :phone, :ip_address, NOW())
    ");

    $stmt->execute([
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => $email,
        'message' => $message,
        'phone' => $phone,
        'ip_address' => $ipAddress,
    ]);

    $transport = Transport::fromDsn($config['mailer_dsn']); // <- Hier deine SMTP-Daten eintragen
    $mailer = new Mailer($transport);

    $emailMessage = (new Email())
        ->from($config['mail_from'])
        ->to($email)
        ->subject('Neue Kontaktanfrage von ' . $firstName . ' ' . $lastName)
        ->text("Nachricht:\n\n" . $message . "\n\nTelefon: " . ($phone ?: 'Nicht angegeben') . "\nE-Mail: " . $email);
    $mailer->send($emailMessage);

    header('Location: /contact.php?success=1');
    exit();


} catch (PDOException $e) {
    http_response_code(500);
    exit('Fehler beim Speichern der Nachricht.');
}