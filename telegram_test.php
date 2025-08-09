<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, max-age=0');
header('X-Content-Type-Options: nosniff');

// Read and ignore any body to avoid logging or processing inputs.
try {
  file_get_contents('php://input');
} catch (Throwable $e) {
  // ignore
}

$token = getenv('TELEGRAM_BOT_TOKEN') ?: '';
$chatId = getenv('TELEGRAM_CHAT_ID') ?: '';
if ($token === '' || $chatId === '') {
  http_response_code(500);
  echo json_encode(['error' => 'Telegram environment variables are not configured']);
  exit;
}

$payload = [
  'chat_id' => $chatId,
  'text' => 'Health check OK (no user data).',
  'disable_web_page_preview' => true,
];

$ch = curl_init("https://api.telegram.org/bot{$token}/sendMessage");
curl_setopt_array($ch, [
  CURLOPT_POST => true,
  CURLOPT_POSTFIELDS => http_build_query($payload),
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_TIMEOUT => 6,
]);
$response = curl_exec($ch);
$err = curl_error($ch);
$httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false || $httpCode < 200 || $httpCode >= 300) {
  http_response_code(502);
  echo json_encode(['error' => $err ?: 'Telegram API call failed', 'status' => $httpCode]);
  exit;
}

echo json_encode(['message' => 'Telegram health check sent.']);