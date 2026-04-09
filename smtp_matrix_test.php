<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/bootstrap/app.php';

use App\Models\SmtpSetting;
use Larafony\Framework\Mail\Transport\SmtpConfig;
use Larafony\Framework\Mail\Transport\SmtpTransport;
use Larafony\Framework\Mail\Transport\ValueObjects\MailEncryption;
use Larafony\Framework\Mail\Transport\ValueObjects\MailPort;
use Larafony\Framework\Mail\Transport\ValueObjects\MailUserInfo;
use Larafony\Framework\Mail\Message\Email;
use Larafony\Framework\Mail\Message\Mailbox;

/** @var SmtpSetting|null $setting */
$setting = SmtpSetting::query()->where('id', '=', 1)->first();
if (!$setting) {
    fwrite(STDERR, "SMTP setting 1 not found\n");
    exit(1);
}

$password = $setting->decryptPassword();
$host = (string) $setting->host;
$user = (string) $setting->username;
$from = (string) ($setting->from_email ?: $setting->username);

$cases = [
    ['label' => 'none:25', 'port' => 25, 'encryption' => null],
    ['label' => 'none:587', 'port' => 587, 'encryption' => null],
    ['label' => 'tls:587', 'port' => 587, 'encryption' => 'tls'],
    ['label' => 'none:465', 'port' => 465, 'encryption' => null],
    ['label' => 'ssl:465', 'port' => 465, 'encryption' => 'ssl'],
    ['label' => 'tls:25', 'port' => 25, 'encryption' => 'tls'],
    ['label' => 'ssl:25', 'port' => 25, 'encryption' => 'ssl'],
];

$message = (new Email())
    ->from(new Mailbox($from))
    ->to(new Mailbox($from))
    ->subject('SMTP matrix probe')
    ->html('<p>SMTP matrix probe</p>');

foreach ($cases as $case) {
    $enc = $case['encryption'] !== null ? MailEncryption::fromScheme($case['encryption']) : null;
    $config = new SmtpConfig(
        host: $host,
        port: MailPort::fromInt($case['port'], $enc),
        userInfo: MailUserInfo::fromString(rawurlencode($user) . ':' . rawurlencode($password)),
        encryption: $enc,
    );

    echo "=== {$case['label']} ===\n";
    try {
        (new SmtpTransport($config))->send($message);
        echo "RESULT: OK\n";
    } catch (Throwable $e) {
        echo 'RESULT: FAIL - ' . get_class($e) . ' - ' . $e->getMessage() . "\n";
    }
    echo "\n";
}
