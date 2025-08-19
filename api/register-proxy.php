<?php
// /is-ortaklar-paneli/api/register-proxy.php
// Amaç: Frontend'den gelen JSON'ı gerçek API'ye (34.44.194.247:3000) server-side POST etmek (CORS yok)

header('Content-Type: application/json; charset=utf-8');

$target = $_GET['target'] ?? '';
$map = [
  'company'    => 'http://34.44.194.247:3000/api/auth/register/company',
  'individual' => 'http://34.44.194.247:3000/api/auth/register/individual',
];

if (!isset($map[$target])) {
  http_response_code(400);
  echo json_encode(['message' => 'Geçersiz target parametresi.']);
  exit;
}

$raw = file_get_contents('php://input');
if ($raw === false) {
  http_response_code(400);
  echo json_encode(['message' => 'Boş istek gövdesi.']);
  exit;
}

$ch = curl_init($map[$target]);
curl_setopt_array($ch, [
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
  CURLOPT_POSTFIELDS => $raw,
  CURLOPT_TIMEOUT => 30,
]);

$response = curl_exec($ch);
$err      = curl_error($ch);
$code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($err) {
  http_response_code(502);
  echo json_encode(['message' => 'Upstream hatası: '.$err]);
  exit;
}

// Upstream'in status ve body’sini aynen geçir
http_response_code($code);
if ($response === false || $response === null || $response === '') {
  echo json_encode(['message' => 'Boş yanıt geldi.']);
} else {
  // Eğer upstream JSON değilse yine string dönebilir; frontend bunu da yakalıyor.
  echo $response;
}
