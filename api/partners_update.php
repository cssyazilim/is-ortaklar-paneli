<?php
// api/partners_update.php
require_once __DIR__ . '/../config/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// DOĞRU IP'Yİ KULLAN
$API  = 'http://34.44.194.247:3001/api/partners/me';
$body = file_get_contents('php://input') ?: '{}';

$headers = [
  'Content-Type: application/json',
  'Accept: application/json',
];

$tok = $_SESSION['accessToken'] ?? '';
if ($tok) {
  if (stripos($tok, 'Bearer ') !== 0) $tok = 'Bearer ' . $tok;
  $headers[] = 'Authorization: ' . $tok;
}

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'POST'); // PUT/PATCH/POST/DELETE

$ch = curl_init($API);
$opts = [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_HTTPHEADER     => $headers,
  CURLOPT_HEADER         => true,
  CURLOPT_TIMEOUT        => 20,
];

if ($method === 'POST') {
  $opts[CURLOPT_POST]       = true;
  $opts[CURLOPT_POSTFIELDS] = $body;
} else { // PUT, PATCH, DELETE, ...
  $opts[CURLOPT_CUSTOMREQUEST] = $method;
  $opts[CURLOPT_POSTFIELDS]    = $body;
}

curl_setopt_array($ch, $opts);

$resp  = curl_exec($ch);
$errno = curl_errno($ch);
$code  = curl_getinfo($ch, CURLINFO_HTTP_CODE) ?: 500;
$hLen  = curl_getinfo($ch, CURLINFO_HEADER_SIZE) ?: 0;
curl_close($ch);

http_response_code($errno ? 502 : $code);
header('Content-Type: application/json; charset=utf-8');

if ($errno) {
  echo json_encode(['error' => 'curl_error', 'detail' => $errno]);
  exit;
}

echo substr($resp ?: '', $hLen) ?: json_encode(['error' => 'empty_response']);
