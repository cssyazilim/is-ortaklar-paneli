<?php
// /api/partners_me.php
require_once __DIR__ . '/../config/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json; charset=utf-8');

// 1) Önce session
$token = $_SESSION['accessToken'] ?? '';

// 2) Authorization header fallback
$hdr = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
if (!$token && preg_match('/Bearer\s+(.+)/i', $hdr, $m)) {
  $token = trim($m[1]);
}

// 3) (Opsiyonel) query ?accessToken=... fallback
if (!$token && isset($_GET['accessToken'])) {
  $token = trim($_GET['accessToken']);
}

error_log('[partners_me] sid='.session_id().' token.len='.strlen($token));

if (!$token) {
  http_response_code(401);
  echo json_encode(['error' => 'Unauthorized', 'reason' => 'no_token']);
  exit;
}

$ch = curl_init('http://34.44.194.247:3001/api/partners/me');
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_CONNECTTIMEOUT => 10,
  CURLOPT_TIMEOUT        => 20,
  CURLOPT_HTTPHEADER     => [
    'Accept: application/json',
    'Authorization: Bearer ' . $token,
  ],
]);

$body = curl_exec($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE) ?: 502;
$err  = curl_error($ch);
curl_close($ch);

http_response_code($http);
echo $err ? json_encode(['error' => 'curl_error', 'detail' => $err]) : ($body !== false ? $body : json_encode(['error' => 'empty_response']));
