<?php
// file: bayi/siparislerim.php
require_once __DIR__ . '/_boot.php';   // profil/profile.php içindeysen: require_once __DIR__ . '/../_boot.php';
require_bayi_role();   
require_once __DIR__ . '/../config/config.php';

/* ================== API Kökü ================== */
if (!defined('API_BASE')) define('API_BASE', 'http://34.44.194.247:3001/api/auth');
if (!defined('API_ROOT')) define('API_ROOT', preg_replace('~/auth/?$~i','', rtrim(API_BASE,'/')));

/* ================== Yardımcılar ================== */
function http_get_json_url(string $url, array $headers=[]): array {
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => $headers ?: ['Accept: application/json'],
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

function partner_bearer(): string {
  foreach ([
    $_SESSION['partnerAccessToken'] ?? null,
    $_SESSION['bayiAccessToken'] ?? null,
    $_SESSION['accessToken'] ?? null,
  ] as $t) {
    if ($t && is_string($t)) return (stripos($t,'Bearer ')===0) ? $t : ('Bearer '.$t);
  }
  return '';
}
function short_no(string $prefix, string $id): string {
  $s = strtoupper(substr(str_replace(['-','_'],'',$id),0,8));
  return $prefix.'-'.$s;
}
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

/* TR para/tarih */
function tr_money(?float $n): string {
  if ($n === null) return '—';
  return '₺'.number_format($n, 0, ',', '.');
}
function tr_date(?string $iso): string {
  if (!$iso) return '—';
  $t = strtotime($iso);
  return $t ? date('d M Y', $t) : '—';
}

/* Sipariş durumu -> rozet */
function order_status_meta(string $s): array {
  $s = strtolower($s);
  if ($s === 'completed') return ['Tamamlandı', 'status-completed'];
  if ($s === 'draft')     return ['Oluşturuldu', 'status-draft'];
  if ($s === 'pending')   return ['Beklemede',   'status-pending'];
  return [ucfirst($s), 'status-pending'];
}
function payment_meta(string $s): array {
  $s = strtolower($s);
  if ($s === 'completed') return ['Tamamlandı', 'status-completed'];
  return ['Bekliyor', 'status-pending'];
}
function earn_meta(string $s): array {
  $s = strtolower($s);
  if ($s === 'completed') return ['Tahsil Edildi', 'status-completed'];
  if ($s === 'draft')     return ['Hesaplandı', 'status-draft'];
  return ['Tahsilat Bekliyor', 'status-pending'];
}

/* ====== Embed kontrolü ====== */
$EMBED = (
  isset($_GET['embed']) ||
  isset($_SERVER['HTTP_X_EMBED']) ||
  (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
);

/* ================== Orders'ı çek ================== */
$ordersError = null;
$ordersRows  = [];
try {
  $bearer = partner_bearer();
  if ($bearer === '') throw new RuntimeException('Token bulunamadı.');

  $q = [];
  if (!empty($_GET['status'])) $q[] = 'status='.urlencode($_GET['status']);
  $url = rtrim(API_ROOT, '/').'/orders'.($q ? ('?'.implode('&',$q)) : '');

  // /api/orders düz DİZİ döndürüyor
  $ordersRows = http_get_json_url($url, ['Accept: application/json','Authorization: '.$bearer]);
} catch (Throwable $e) {
  $ordersError = $e->getMessage();
}

/* ViewModel */
$orders = array_map(function($o){
  $custName = $o['title'] ?? $o['name'] ?? '—';
  return [
    'id'         => (string)($o['id'] ?? ''),
    'no'         => short_no('SIP', (string)($o['id'] ?? '')),
    'customer'   => $custName,
    'total'      => isset($o['total_amount']) ? (float)$o['total_amount'] : null,
    'status'     => strtolower($o['status'] ?? 'pending'),
    'created_at' => $o['created_at'] ?? null,
  ];
}, $ordersRows ?? []);

/* ====== Eğer embed DEĞİLSE parent sayfaya yönlendir ======
   Parent (bayi.php) JS, r parametresini okuyup:
     - orders sekmesini açacak
     - history.replaceState ile URL'i /is-ortaklar-paneli/bayi/siparislerim.php yapacak
*/
/* en üstte */
$EMBED = isset($_GET['embed']) && $_GET['embed'] == '1';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Siparişlerim (Embed)</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .card-shadow { box-shadow: 0 4px 6px -1px rgba(0,0,0,.1), 0 2px 4px -1px rgba(0,0,0,.06); }
    .status-completed { background:#dcfce7; color:#166534; }
    .status-pending   { background:#fef3c7; color:#92400e; }
    .status-draft     { background:#e0e7ff; color:#3730a3; }
  </style>
</head>
<body class="bg-transparent">

<!-- Tek beyaz kart (rounded) -->
<section class="max-w-7xl mx-auto my-6 rounded-2xl bg-white ring-1 ring-gray-100 card-shadow overflow-hidden">
  <div class="p-4 sm:p-6 lg:p-8">
    <div class="mb-6">
      <h2 class="text-2xl font-bold text-gray-900 mb-2">Sipariş Yönetimi</h2>
      <p class="text-gray-600">Sipariş takibi, teslimat ve ödeme durumu</p>
    </div>

    <!-- Filtreler -->
    <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6 mb-6">
      <div class="flex flex-col sm:flex-row sm:items-center gap-3 justify-between">
        <div class="flex items-center gap-3">
          <label for="statusSel" class="text-sm text-gray-700">Durum:</label>
          <select id="statusSel" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <?php
              $cur = $_GET['status'] ?? '';
              $opts = ['' => 'Tümü', 'pending' => 'Beklemede', 'draft' => 'Oluşturuldu', 'completed' => 'Tamamlandı'];
              foreach ($opts as $val=>$lbl) {
                $sel = ($cur===$val) ? 'selected' : '';
                echo '<option value="'.h($val).'" '.$sel.'>'.h($lbl).'</option>';
              }
            ?>
          </select>
          <button id="applyBtn" class="ml-1 bg-indigo-600 text-white text-sm px-3 py-2 rounded-lg hover:bg-indigo-700">Uygula</button>
          <?php if ($ordersError): ?>
            <span class="ml-4 text-sm text-red-600">API Hatası: <?= h($ordersError) ?></span>
          <?php endif; ?>
        </div>
        <div class="text-sm text-gray-600">Toplam: <strong><?= count($orders) ?></strong> sipariş</div>
      </div>
    </div>

    <!-- Liste -->
    <div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
      <div class="p-4 sm:p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Sipariş Listesi</h3>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sipariş No</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Müşteri</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tutar</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sipariş Durumu</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ödeme Durumu</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hakediş</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tarih</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <?php if (empty($orders)): ?>
              <tr>
                <td colspan="8" class="px-6 py-10 text-center text-gray-500">Kayıtlı sipariş bulunamadı.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($orders as $o):
                [$lbl,$cls]  = order_status_meta($o['status']);
                [$pl,$pc]    = payment_meta($o['status']);
                [$el,$ec]    = earn_meta($o['status']);
              ?>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= h($o['no']) ?></td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= h($o['customer']) ?></td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= tr_money($o['total']) ?></td>
                  <td class="px-6 py-4 whitespace-nowrap"><span class="px-2 py-1 text-xs font-semibold rounded-full <?= h($cls) ?>"><?= h($lbl) ?></span></td>
                  <td class="px-6 py-4 whitespace-nowrap"><span class="px-2 py-1 text-xs font-semibold rounded-full <?= h($pc) ?>"><?= h($pl) ?></span></td>
                  <td class="px-6 py-4 whitespace-nowrap"><span class="px-2 py-1 text-xs font-semibold rounded-full <?= h($ec) ?>"><?= h($el) ?></span></td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= tr_date($o['created_at']) ?></td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button onclick="viewOrder('<?= h($o['id']) ?>')" class="text-indigo-600 hover:text-indigo-900">Görüntüle</button>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>

<script>
// Filtre uygula
document.getElementById('applyBtn')?.addEventListener('click', ()=>{
  const v = document.getElementById('statusSel').value;
  const url = new URL(location.href);
  if (v) url.searchParams.set('status', v); else url.searchParams.delete('status');
  location.href = url.toString();
});

// Detay (örnek)
function viewOrder(id){
  alert('Sipariş detayı açılacak: ' + id);
}

/* ---- Parent yüksekliği ---- */
function postHeight(){
  try {
    const h = Math.max(document.body.scrollHeight, document.documentElement.scrollHeight);
    parent.postMessage({ type: 'siparislerim-height', height: h }, '*');
  } catch(_) {}
}
new ResizeObserver(postHeight).observe(document.body);
window.addEventListener('load', postHeight);

/* ---- Parent'tan yeniden yükleme ---- */
window.addEventListener('message', (e)=>{
  if (e?.data?.type === 'orders-reload') {
    location.reload();
  }
});
</script>

</body>
</html>
