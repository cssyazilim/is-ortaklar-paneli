<?php
// bayi/teklifler.php
session_start();
if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'bayi') {
  header('Location: /is-ortaklar-paneli/login.php'); exit;
}
/* en üstte */
$EMBED = isset($_GET['embed']) && $_GET['embed'] == '1';

require_once __DIR__ . '/../config/config.php';

/* ===== API kökleri ===== */
if (!defined('API_BASE')) define('API_BASE', 'http://34.44.194.247:3001/api/auth');
if (!defined('API_ROOT')) define('API_ROOT', preg_replace('~/auth/?$~i','', rtrim(API_BASE,'/')));

/* ===== Basit GET ===== */
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

/* ===== JWT payload (doğrulamasız) ===== */
function jwt_payload(string $jwt): array {
  $p = explode('.', $jwt);
  if (count($p) < 2) return [];
  $payload = $p[1] . str_repeat('=', (4 - strlen($p[1]) % 4) % 4);
  $json = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);
  return is_array($json) ? $json : [];
}

/* ===== Status -> rozet ===== */
function status_meta(string $s): array {
  $s = strtolower($s);
  if ($s === 'active')   return ['Müşteri Onayladı', 'status-approved bg-green-100 text-green-700'];
  if ($s === 'pending')  return ['İncelemede',        'status-review bg-yellow-100 text-yellow-700'];
  if ($s === 'draft')    return ['Teklif Hazır',      'status-ready bg-emerald-100 text-emerald-700'];
  if ($s === 'rejected' || $s === 'passive') return ['Reddedildi','bg-red-100 text-red-700'];
  return ['İncelemede', 'status-review bg-yellow-100 text-yellow-700'];
}

/* ===== TR tarih helper ===== */
function tr_date(?string $iso): string {
  if (!$iso) return '-';
  $t = strtotime($iso); if (!$t) return '-';
  return date('d.m.Y', $t);
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
    $headers = ['Authorization: Bearer '.$token];
    if (!empty($_SESSION['user']['partner_id'])) {
      $headers[] = 'X-Partner-Id: '.$_SESSION['user']['partner_id'];
    }
    // Not: Şu an customers'dan besleniyor. İleride /quotes endpoint'iniz olursa burayı değiştirin.
    $resp  = http_get_json(API_ROOT, 'customers', $headers);
    $items = $resp['items'] ?? [];

    foreach ($items as $it) {
      $id     = (string)($it['id'] ?? '');
      $short  = strtoupper(substr(str_replace('-','',$id), 0, 8));
      $no     = $short ? ('CUS-'.$short) : 'CUS-XXXXXX';
      $title  = $it['title'] ?? ($it['name'] ?? '-');
      $tarih  = $it['created_at'] ?? $it['updated_at'] ?? null;
      $status = $it['status'] ?? 'pending';
      [$label,$cls] = status_meta($status);

      $rows[] = [
        'no'     => $no,
        'musteri'=> $title,
        'tutar'  => null, // customers API tutar vermiyor
        'tarih'  => $tarih,
        'label'  => $label,
        'cls'    => $cls,
        'id'     => $id,
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
        <button onclick="createNewQuote()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
          Yeni Teklif Oluştur
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
                    <?= $r['tutar'] !== null ? '₺'.number_format($r['tutar'],0,',','.') : '-' ?>
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
                      <button onclick="sendQuoteToCustomer('<?= htmlspecialchars($r['id'], ENT_QUOTES, 'UTF-8') ?>')" class="text-indigo-600 hover:text-indigo-900">Müşteriye Gönder</button>
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
