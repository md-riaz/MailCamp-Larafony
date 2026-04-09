import smtplib
import socket

HOST = 'mail.alpha.net.bd'
USER = 'mdriaz@alpha.net.bd'
PASSWORD = None

import subprocess, json, textwrap, os, sys
# fetch password via php so we don't hardcode it
php = subprocess.run(
    ['php', '-r', 'require "vendor/autoload.php"; require "bootstrap/app.php"; $s=App\\Models\\SmtpSetting::query()->where("id","=",1)->first(); echo $s ? $s->decryptPassword() : "";'],
    cwd='/var/project/MailCamp-Larafony', capture_output=True, text=True, timeout=30
)
PASSWORD = php.stdout.strip()
if not PASSWORD:
    print('FAILED: could not load SMTP password')
    sys.exit(1)

cases = [
    ('none:25', 25, 'none'),
    ('none:587', 587, 'none'),
    ('tls:587', 587, 'tls'),
    ('none:465', 465, 'none'),
    ('ssl:465', 465, 'ssl'),
    ('tls:25', 25, 'tls'),
    ('ssl:25', 25, 'ssl'),
]

socket.setdefaulttimeout(12)

for label, port, mode in cases:
    print(f'=== {label} ===')
    try:
        if mode == 'ssl':
            server = smtplib.SMTP_SSL(HOST, port, timeout=12)
        else:
            server = smtplib.SMTP(HOST, port, timeout=12)
        server.ehlo()
        if mode == 'tls':
            server.starttls()
            server.ehlo()
        code, msg = server.login(USER, PASSWORD)
        print(f'OK auth code={code} msg={msg!r}')
        try:
            server.quit()
        except Exception:
            pass
    except Exception as e:
        print(f'FAIL {type(e).__name__}: {e}')
    print()
