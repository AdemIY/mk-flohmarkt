<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../includes/db.php';

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

$db = connect();
$config = require __DIR__ . '/../../config.php';

$email = $_POST['email'] ?? $_GET['email'] ?? '';
if (!$email) {
    http_response_code(400);
    echo 'Keine E-Mail übergeben.';
    exit;
}

header('Content-Type: application/json');

function jsonResponse($status, $message, $httpCode = 200)
{
    http_response_code($httpCode);
    echo json_encode(['status' => $status, 'message' => $message]);
    exit;
}

$email = $_POST['email'] ?? $_GET['email'] ?? '';
if (!$email) {
    jsonResponse('error', 'Keine E-Mail übergeben.', 400);
}

try {
    // 1. Nutzer finden
    $stmt = $db->prepare("SELECT id, password FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        jsonResponse('error', 'Diese E-Mail ist nicht registriert.', 404);
    }

    if (!empty($user['password'])) {
        jsonResponse('error', 'Für diese E-Mail ist bereits ein Passwort gesetzt.', 200);
    }

    // 2. Spam-Schutz
    $stmt = $db->prepare("SELECT sent_at FROM password_resets WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user['id']]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing && strtotime($existing['sent_at']) > strtotime('-2 minutes')) {
        jsonResponse('wait', '⏳ Bitte warte ein paar Minuten, bevor du eine neue E-Mail für anforderst.', 429);
    }

// 3. Token generieren
    $token = bin2hex(random_bytes(32));
    $expires = (new DateTime('+1 hour'))->format('Y-m-d H:i:s');
    $sentAt = (new DateTime())->format('Y-m-d H:i:s');

    if ($existing) {
        $stmt = $db->prepare("UPDATE password_resets SET token = :token, expires_at = :expires, sent_at = :sent_at WHERE user_id = :user_id");
    } else {
        $stmt = $db->prepare("INSERT INTO password_resets (user_id, token, expires_at, sent_at) VALUES (:user_id, :token, :expires, :sent_at)");
    }

    $stmt->execute([
        'user_id' => $user['id'],
        'token' => $token,
        'expires' => $expires,
        'sent_at' => $sentAt
    ]);
// 4. Mail versenden
    $link = "https://starter.ddev.site/set-password.php?token=$token";

    $transport = Transport::fromDsn($config['mailer_dsn']);
    $mailer = new Mailer($transport);

    $emailObject = (new Email())
        ->from($config['mail_from'])
        ->to($email)
        ->subject('Setze dein Passwort')
        ->text("Hier kannst du dein Passwort setzen (gültig 1 Stunde):\n\n$link");

    $mailer->send($emailObject);
    jsonResponse('success', '✅ Link zum Setzen deines Passworts wurde gesendet. Bitte schaue in dein E-Mail Posteingang');

} catch (Exception $e) {
    error_log('Fehler beim Senden des Passwort-Links: ' . $e->getMessage());
    jsonResponse('error', 'Interner Serverfehler.', 500);
}
