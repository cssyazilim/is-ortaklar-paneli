<?php
// bayi/teklifler.php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'bayi') {
  header('Location: /is-ortaklar-paneli/login.php'); exit;
}

$EMBED = isset($_GET['embed']) && $_GET['embed'] == '1';

require_once __DIR__ . '/../config/config.php';

/* ===== API kökleri ===== */
if (!defined('API_BASE')) define('API_BASE', 'http://34.44.194.247:3001/api/auth');
if (!defined('API_ROOT')) define('API_ROOT', preg_replace('~/auth/?$~i','', rtrim(API_BASE,'/')));

/* ===== Helpers ===== */
function http_get_json(string $base, string $path, array $headers=[]): array {
  $url = rtrim($base,'/').'/'.ltrim($path,'/');
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => array_merge(['Accept: application/json'], $headers),
    CURLOPT_TIMEOUT        => 20,
  ]);
  $raw  = curl_exec($ch);
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $err  = curl_error($ch);
  curl_close($ch);
  if ($raw === false) throw new RuntimeException('Sunucuya ulaşılamadı: '.$err);
  $json = json_decode($raw, true);
  if (!is_array($json)) throw new RuntimeException('Geçersiz yanıt: '.$raw);
  if ($code < 200 || $code >= 300) {
    $msg = $json['message'] ?? $json['error'] ?? ('HTTP '.$code);
    throw new RuntimeException($msg);
  }
  return $json;
}

function jwt_payload(string $jwt): array {
  $p = explode('.', $jwt);
  if (count($p) < 2) return [];
  $payload = $p[1] . str_repeat('=', (4 - strlen($p[1]) % 4) % 4);
  $json = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);
  return is_array($json) ? $json : [];
}

function status_meta(string $s): array {
  $s = strtolower($s);
  return match($s) {
    'accepted' => ['Müşteri Onayladı',      'bg-green-100 text-green-700'],
    'sent'     => ['Müşteriye Gönderildi',  'bg-indigo-100 text-indigo-700'],
    'draft'    => ['Taslak',                'bg-gray-100 text-gray-700'],
    'rejected' => ['Reddedildi',            'bg-red-100 text-red-700'],
    'expired'  => ['Süresi Doldu',          'bg-orange-100 text-orange-700'],
    default    => ['İncelemede',            'bg-yellow-100 text-yellow-700'],
  };
}

function tr_date(?string $iso): string {
  if (!$iso) return '-';
  $t = strtotime($iso); if (!$t) return '-';
  return date('d.m.Y', $t);
}

// "Bearer " ön eki yoksa ekle
function bearer(string $token): string {
  return preg_match('/^Bearer\s+/i', $token) ? $token : ('Bearer '.$token);
}

/* ===== Partner scope’lu token seç ===== */
$tokenUser     = $_SESSION['accessToken']        ?? null;
$tokenPartner1 = $_SESSION['partnerAccessToken'] ?? null;
$tokenPartner2 = $_SESSION['bayiAccessToken']    ?? null;

$token = null;
foreach ([$tokenPartner1, $tokenPartner2, $tokenUser] as $cand) {
  if (!$cand) continue;
  $p = jwt_payload($cand);
  $scope = $p['scope'] ?? $p['scopes'] ?? '';
  if ($scope === 'partner' || (is_array($scope) && in_array('partner', $scope, true))) {
    $token = $cand; break;
  }
}

/* ===== Veriyi çek ===== */
$apiError = null; $rows = [];
if (!$token) {
  $apiError = 'Bu liste için partner (bayi) scope gereklidir. Bayi hesabıyla giriş yapın.';
} else {
  try {
    $headers = ['Authorization: '.bearer($token)];
    if (!empty($_SESSION['user']['partner_id'])) {
      $headers[] = 'X-Partner-Id: '.$_SESSION['user']['partner_id'];
    }

    // Filtre/paginasyon
    $qs = [];
    if (isset($_GET['status']) && $_GET['status'] !== '') $qs[] = 'status='.rawurlencode($_GET['status']);
    $limit  = isset($_GET['limit'])  ? max(1, min(200, (int)$_GET['limit']))  : 50;
    $offset = isset($_GET['offset']) ? max(0, (int)$_GET['offset']) : 0;
    $qs[] = "limit={$limit}";
    $qs[] = "offset={$offset}";
    $path = 'quotes'.(count($qs) ? ('?'.implode('&', $qs)) : '');

    // GET /api/quotes -> dizi döner
    $quotes = http_get_json(API_ROOT, $path, $headers);

    foreach ($quotes as $q) {
      $id       = (string)($q['id'] ?? '');
      $short    = strtoupper(substr(str_replace('-','',$id), 0, 8));
      $no       = $short ? ('QUO-'.$short) : 'QUO-XXXXXX';
      $title    = $q['title'] ?? $q['name'] ?? '-';
      $amount   = $q['total_amount'] ?? null;
      $currency = $q['currency'] ?? 'TRY';
      $status   = $q['status'] ?? 'pending';
      $tarih    = $q['created_at'] ?? $q['validity_date'] ?? null;
      [$label,$cls] = status_meta($status);

      // Tutarı GÖRÜNTÜLEME İÇİN formatla (string)
      $tutarStr = null;
      if (is_numeric($amount)) {
        $num = (float)$amount;
        if ($currency === 'TRY') {
          $tutarStr = '₺'.number_format($num, 0, ',', '.');
        } elseif ($currency === 'USD') {
          $tutarStr = '$'.number_format($num, 0, ',', '.');
        } elseif ($currency === 'EUR') {
          $tutarStr = '€'.number_format($num, 0, ',', '.');
        } else {
          $tutarStr = number_format($num, 0, ',', '.').' '.$currency;
        }
      }

      $rows[] = [
        'no'      => $no,
        'musteri' => $title,
        'tutar'   => $tutarStr,   // tabloya string olarak basacağız
        'tarih'   => $tarih,
        'label'   => $label,
        'cls'     => $cls,
        'id'      => $id,
      ];
    }
  } catch (Throwable $e) {
    $apiError = $e->getMessage();
  }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Teklifler</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/bayi.css">
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">

  <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
      <h2 class="text-3xl font-bold text-gray-900 mb-2">Teklif Yönetimi</h2>
      <p class="text-gray-600">Teklif talepleri, hazırlama ve takip işlemleri</p>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
      <div class="flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-900">Teklif İşlemleri</h3>
        <button type="button" onclick="createNewQuote()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg">
          Teklif Talebi Oluştur
        </button>
      </div>
    </div>

    <div class="bg-white rounded-xl shadow-md border border-gray-100">
      <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Teklif Listesi</h3>
        <?php if ($apiError): ?>
          <p class="mt-2 text-sm text-red-600">API Hatası: <?= htmlspecialchars($apiError, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full min-w-[600px]">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teklif No</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Müşteri</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tutar</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Tarih</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <?php if (!empty($rows)): ?>
              <?php foreach ($rows as $r): ?>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    <?= htmlspecialchars($r['no'], ENT_QUOTES, 'UTF-8') ?>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <?= htmlspecialchars($r['musteri'], ENT_QUOTES, 'UTF-8') ?>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <?= $r['tutar'] !== null ? htmlspecialchars($r['tutar'], ENT_QUOTES, 'UTF-8') : '-' ?>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 py-1 text-xs font-semibold rounded-full <?= htmlspecialchars($r['cls'], ENT_QUOTES, 'UTF-8') ?>">
                      <?= htmlspecialchars($r['label'], ENT_QUOTES, 'UTF-8') ?>
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden sm:table-cell">
                    <?= tr_date($r['tarih']) ?>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
                      <button onclick="sendQuoteToCustomer('<?= htmlspecialchars($r['id'], ENT_QUOTES, 'UTF-8') ?>')" class="text-indigo-600 hover:text-indigo-900">Gönder</button>
                      <button onclick="editQuote('<?= htmlspecialchars($r['id'], ENT_QUOTES, 'UTF-8') ?>')" class="text-gray-600 hover:text-gray-900">Düzenle</button>
                      <button onclick="viewQuote('<?= htmlspecialchars($r['id'], ENT_QUOTES, 'UTF-8') ?>')" class="text-gray-600 hover:text-gray-900">Görüntüle</button>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                  Aradığınız kriterlere uygun teklif bulunamadı
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <link rel="stylesheet" href="/is-ortaklar-paneli/bayi/bayi.css">
  <script src="/is-ortaklar-paneli/bayi/bayi.js"></script>
</body>
</html>
