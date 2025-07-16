<?php

use Symfony\Component\Dotenv\Dotenv;

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = new Dotenv();
$dotenv->loadEnv(__DIR__ . '/.env');

return [
    'mailer_dsn' => $_ENV['MAILER_DSN'],
    'mail_from' => $_ENV['MAIL_FROM'],
];