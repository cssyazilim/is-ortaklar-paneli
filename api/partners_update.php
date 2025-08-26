<?php
// api/partners_update.php
require_once __DIR__ . '/../config/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$API = 'http://34.44.194.247:3001/api/partners/me'; // Node API
$in  = file_get_contents('php://input');

$headers = [
  'Content-Type: application/json',
  'Accept: application/json'
];

$tok = $_SESSION['accessToken'] ?? '';
if ($tok) {
  if (stripos($tok, 'Bearer ') !== 0) $tok = 'Bearer ' . $tok;
  $headers[] = 'Authorization: ' . $tok;
}

$ch = curl_init($API);
curl_setopt_array($ch, [
  CURLOPT_POST           => true,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_HTTPHEADER     => $headers,
  CURLOPT_POSTFIELDS     => $in,
  CURLOPT_HEADER         => true
]);

$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$hLen = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$body = substr($resp, $hLen);
curl_close($ch);

http_response_code($code);
header('Content-Type: application/json; charset=utf-8');
echo $body;
