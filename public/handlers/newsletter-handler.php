<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../vendor/autoload.php';
$config = require __DIR__ . '/../../config.php';

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

function generateToken($length = 64)
{
    return bin2hex(random_bytes($length / 2));
}

$email = trim($_POST['email'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit('Ungültige E-Mail-Adresse.');
}
try {
    $db = connect();
    // 1. User finden oder anlegen
    $stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $userId = $user['id'];
    } else {
        $stmt = $db->prepare("INSERT INTO users (email, newsletter_opt_in) VALUES (:email, 0)");
        $stmt->execute(['email' => $email]);
        $userId = $db->lastInsertId();
    }

// 2. Token erzeugen
    $token = generateToken();
    $expires = date('Y-m-d H:i:s', strtotime('+1 day'));

    $stmt = $db->prepare("INSERT INTO newsletter_tokens (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)");
    $stmt->execute([
        'user_id' => $userId,
        'token' => $token,
        'expires_at' => $expires
    ]);

// 3. Mail senden
    $transport = Transport::fromDsn($config['mailer_dsn']); // <- Hier deine SMTP-Daten eintragen
    $mailer = new Mailer($transport);
    $link = "https://starter.ddev.site/handlers/newsletter-confirm.php?token=$token";

    $emailMessage = (new Email())
        ->from($config['mail_from'])
        ->to($email)
        ->subject('E-Mail Adresse Bestätigen')
        ->text($link);
    $mailer->send($emailMessage);

// 4. Rückmeldung
    echo "Bitte prüfe deine E-Mails und bestätige die Anmeldung.";
    // Statt echo ...
    header('Location: /?success=1');
    exit;

} catch (Exception $e) {
    exit($e->getMessage());
}
