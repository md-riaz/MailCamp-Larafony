<?php
$basePath = rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/');
header('Location: ' . $basePath . '/campaigns/' . $campaign->id . '?workspace=edit', true, 302);
exit;
