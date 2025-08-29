<?php
// /is-ortaklar-paneli/bayi/teklif-olustur.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/_boot.php';
require_bayi_role();

/* ========================= API kökleri ========================= */
if (!defined('API_BASE')) define('API_BASE', 'http://34.44.194.247:3001/api/auth');
if (!defined('API_ROOT')) define('API_ROOT', preg_replace('~/auth/?$~i','', rtrim(API_BASE,'/')));

/* ========================= Helpers ========================= */
if (!function_exists('jwt_payload')) {
  function jwt_payload(string $jwt): array {
    $p = explode('.', $jwt);
    if (count($p) < 2) return [];
    $b64 = $p[1] . str_repeat('=', (4 - strlen($p[1]) % 4) % 4);
    $json = json_decode(base64_decode(strtr($b64, '-_', '+/')), true);
    return is_array($json) ? $json : [];
  }
}
if (!function_exists('find_jwt_in_array')) {
  function find_jwt_in_array($arr): string {
    $it = new RecursiveIteratorIterator(new RecursiveArrayIterator((array)$arr));
    foreach ($it as $v) {
      if (is_string($v) && preg_match('/^[A-Za-z0-9\-_]+\.[A-Za-z0-9\-_]+\.[A-Za-z0-9\-_]+$/', $v)) return $v;
    }
    return '';
  }
}
if (!function_exists('partner_bearer')) {
  function partner_bearer(): string {
    foreach ([
      $_SESSION['partnerAccessToken'] ?? null,
      $_SESSION['bayiAccessToken']    ?? null,
      $_SESSION['accessToken']        ?? null,
      $_SESSION['access_token']       ?? null,
      $_SESSION['token']              ?? null,
      $_SERVER['HTTP_AUTHORIZATION']  ?? null,
      $_COOKIE['Authorization']       ?? null,
      find_jwt_in_array($_SESSION ?? []),
    ] as $t) {
      if (!$t || !is_string($t)) continue;
      if (stripos($t, 'Bearer ') === 0) return $t;
      if (preg_match('/^[A-Za-z0-9\-_]+\.[A-Za-z0-9\-_]+\.[A-Za-z0-9\-_]+$/', $t)) return 'Bearer '.$t;
    }
    return '';
  }
}
if (!function_exists('resolve_partner_id')) {
  function resolve_partner_id(?string $bearerTok = null): string {
    if (!empty($_SESSION['user']['partner_id'])) return (string)$_SESSION['user']['partner_id'];
    $b = $bearerTok ?: partner_bearer();
    if ($b) {
      $payload = jwt_payload(str_replace('Bearer ', '', $b));
      if (!empty($payload['partner_id'])) return (string)$payload['partner_id'];
      if (!empty($payload['user']['partner_id'])) return (string)$payload['user']['partner_id'];
    }
    return '';
  }
}
if (!function_exists('http_get_json_url')) {
  function http_get_json_url(string $url, array $headers=[]): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HTTPHEADER     => ($headers ?: ['Accept: application/json']),
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
}
if (!function_exists('renderStatusBadge')) {
  function renderStatusBadge($status): string {
    $status = strtolower((string)$status);
    $map = [
      'draft'    => ['Taslak',     'bg-yellow-100 text-yellow-800'],
      'sent'     => ['Gönderildi', 'bg-blue-100 text-blue-800'],
      'approved' => ['Onaylandı',  'bg-green-100 text-green-800'],
      'rejected' => ['Reddedildi', 'bg-red-100 text-red-800'],
    ];
    $label = $map[$status][0] ?? ($status ? ucfirst($status) : 'Taslak');
    $cls   = $map[$status][1] ?? 'bg-gray-100 text-gray-800';
    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium '.$cls.'">'.$label.'</span>';
  }
}
if (!function_exists('iso_to_ymd')) {
  function iso_to_ymd(?string $iso): string {
    if (!$iso) return '';
    $t = strtotime($iso);
    return $t ? date('Y-m-d', $t) : '';
  }
}

/* ==================== AJAX PROXY: POST /quotes ==================== */
if (isset($_GET['action']) && $_GET['action']==='create' && $_SERVER['REQUEST_METHOD']==='POST') {
  header('Content-Type: application/json; charset=utf-8');
  try {
    $bearer = partner_bearer();
    if ($bearer === '') { http_response_code(401); echo json_encode(['error'=>'unauthorized']); exit; }
    $partnerId = resolve_partner_id($bearer);
    if ($partnerId === '') { http_response_code(400); echo json_encode(['error'=>'partner_id_missing']); exit; }

    $raw = file_get_contents('php://input');
    if ($raw === '' || $raw === false) { http_response_code(400); echo json_encode(['error'=>'empty_body']); exit; }

    $headers = ['Accept: application/json','Content-Type: application/json','Authorization: '.$bearer,'X-Partner-Id: '.$partnerId];

    $ch = curl_init(rtrim(API_ROOT,'/').'/quotes');
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_POST           => true,
      CURLOPT_HTTPHEADER     => $headers,
      CURLOPT_POSTFIELDS     => $raw,
      CURLOPT_TIMEOUT        => 30,
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($resp === false) { http_response_code(502); echo json_encode(['error'=>'upstream_unreachable','detail'=>$err], JSON_UNESCAPED_UNICODE); exit; }
    $json = json_decode($resp, true);
    if (!is_array($json)) { http_response_code($code ?: 500); echo json_encode(['error'=>'bad_upstream_response','raw'=>$resp], JSON_UNESCAPED_UNICODE); exit; }

    http_response_code($code ?: 200);
    echo json_encode($json, JSON_UNESCAPED_UNICODE);
  } catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error'=>'server','detail'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
  }
  exit;
}

/* ==================== Sayfa verileri: quote/customers/products ==================== */
$bearer = partner_bearer();
$partnerId = resolve_partner_id($bearer);

$customerOptions = [];
$productOptions  = [];
$customersError  = null;
$productsError   = null;
$quote           = null;

// Tekil teklif (düzenleme) – helper’lar tanımlandıktan sonra
if (!empty($_GET['id']) && $bearer !== '') {
  try {
    $h = ['Accept: application/json','Authorization: '.$bearer];
    if ($partnerId !== '') $h[] = 'X-Partner-Id: '.$partnerId;
    $quote = http_get_json_url(rtrim(API_ROOT,'/').'/quotes/'.rawurlencode($_GET['id']), $h);
  } catch (Throwable $e) { $quote = null; }
}

try {
  if ($bearer === '') throw new RuntimeException('Unauthorized');
  $headers = ['Accept: application/json','Authorization: '.$bearer];
  if ($partnerId !== '') $headers[] = 'X-Partner-Id: '.$partnerId;

  // Müşteriler
  $cData = http_get_json_url(rtrim(API_ROOT,'/').'/customers?limit=200', $headers);
  foreach (($cData['items'] ?? []) as $it) {
    $id   = (string)($it['id'] ?? '');
    $name = $it['title'] ?? $it['name'] ?? $it['full_name'] ?? $it['company_name'] ?? '—';
    if ($id !== '') $customerOptions[] = ['id'=>$id,'name'=>$name];
  }

  // Ürünler
  $pData = http_get_json_url(rtrim(API_ROOT,'/').'/products?limit=200', $headers);
  foreach (($pData['items'] ?? []) as $p) {
    $pid   = (string)($p['id'] ?? '');
    $pname = $p['name'] ?? $p['title'] ?? 'Ürün';
    $price = isset($p['price']) ? (float)$p['price'] : 0.0;
    if ($pid !== '') $productOptions[] = ['id'=>$pid,'name'=>$pname,'price'=>$price];
  }
} catch (Throwable $e) {
  if (!$customerOptions) $customersError = $e->getMessage();
  if (!$productOptions)  $productsError  = $e->getMessage();
}

/* ==================== View ==================== */
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Yeni Teklif Oluştur - ERP Yönetim Sistemi</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .gradient-bg { background: linear-gradient(135deg,#f3f4f6 0%,#e5e7eb 100%); }
    .card-shadow { box-shadow:0 4px 6px -1px rgba(0,0,0,.1),0 2px 4px -1px rgba(0,0,0,.06); }
    .form-input { transition: all .2s ease; }
    .form-input:focus { box-shadow:0 0 0 3px rgba(59,130,246,.1); }
    .item-row { transition:all .25s ease; }
    .item-row.removing { opacity:.0; transform:translateX(-100%); }
    .loading-spinner { animation: spin 1s linear infinite; } @keyframes spin { from{transform:rotate(0)} to{transform:rotate(360deg)} }
  </style>
</head>
<body class="min-h-screen gradient-bg">
  <script>
    // PHP'den JS'e
    window.PRODUCTS = <?= json_encode($productOptions, JSON_UNESCAPED_UNICODE) ?>;
    window.PREFILL  = <?= json_encode([
      'customer_id'   => $quote['customer_id'] ?? '',
      'currency'      => $quote['currency']    ?? 'TRY',
      'validity_date' => iso_to_ymd($quote['validity_date'] ?? null),
      'notes'         => $quote['notes']       ?? '',
      'status'        => $quote['status']      ?? 'draft',
      'items'         => $quote['items']       ?? [],
    ], JSON_UNESCAPED_UNICODE) ?>;
  </script>

  <main class="max-w-4xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <form id="quoteForm" class="space-y-6">
      <!-- Temel Bilgiler -->
      <div class="bg-white rounded-lg card-shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
          <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          Temel Bilgiler
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Müşteri -->
          <div>
            <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-2">Müşteri <span class="text-red-500">*</span></label>
            <?php if (!empty($customersError)): ?>
              <p class="text-sm text-red-600 mb-2">Müşteri listesi yüklenemedi: <?= htmlspecialchars($customersError, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
            <select id="customer_id" name="customer_id" required class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
              <option value="">Müşteri seçiniz</option>
              <?php
              $selectedCustomer = $quote['customer_id'] ?? '';
              foreach ($customerOptions as $c):
                $sel = ($selectedCustomer && $selectedCustomer === $c['id']) ? ' selected' : '';
              ?>
                <option value="<?= htmlspecialchars($c['id'], ENT_QUOTES, 'UTF-8') ?>"<?= $sel ?>>
                  <?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Para birimi -->
          <div>
            <label for="currency" class="block text-sm font-medium text-gray-700 mb-2">Para Birimi <span class="text-red-500">*</span></label>
            <?php $cur = $quote['currency'] ?? 'TRY'; ?>
            <select id="currency" name="currency" required class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
              <option value="TRY"<?= $cur==='TRY'?' selected':''; ?>>TRY - Türk Lirası</option>
              <option value="USD"<?= $cur==='USD'?' selected':''; ?>>USD - Amerikan Doları</option>
              <option value="EUR"<?= $cur==='EUR'?' selected':''; ?>>EUR - Euro</option>
            </select>
          </div>

          <!-- Geçerlilik tarihi -->
          <div>
            <label for="validity_date" class="block text-sm font-medium text-gray-700 mb-2">Geçerlilik Tarihi</label>
            <input type="date" id="validity_date" name="validity_date"
                   value="<?= htmlspecialchars(iso_to_ymd($quote['validity_date'] ?? null), ENT_QUOTES, 'UTF-8') ?>"
                   min="<?= date('Y-m-d') ?>"
                   class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
          </div>

          <!-- Durum (DB -> status) -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Durum</label>
            <div class="px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg">
              <?= renderStatusBadge($quote['status'] ?? 'draft') ?>
            </div>
          </div>
        </div>

        <!-- Notlar -->
        <div class="mt-6">
          <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notlar</label>
          <textarea id="notes" name="notes" rows="3" placeholder="Teklif ile ilgili notlarınızı yazın..." class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none resize-none"><?= htmlspecialchars($quote['notes'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>
      </div>

      <!-- Kalemler -->
      <div class="bg-white rounded-lg card-shadow p-6">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-lg font-semibold text-gray-900 flex items-center">
            <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            Teklif Kalemleri
          </h2>
          <button type="button" onclick="addItem()" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Kalem Ekle
          </button>
        </div>

        <!-- Head -->
        <div class="hidden md:grid grid-cols-12 gap-4 mb-4 p-3 bg-gray-50 rounded-lg text-sm font-medium text-gray-700">
          <div class="col-span-4">Ürün</div>
          <div class="col-span-2">Miktar</div>
          <div class="col-span-2">Birim Fiyat</div>
          <div class="col-span-2">İndirim</div>
          <div class="col-span-1">Toplam</div>
          <div class="col-span-1">İşlem</div>
        </div>

        <div id="itemsContainer" class="space-y-4"></div>

        <div id="noItemsMessage" class="text-center py-8 border-2 border-dashed border-gray-300 rounded-lg">
          <svg class="mx-auto w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
          <p class="text-gray-500 mb-4">Henüz teklif kalemi eklenmemiş</p>
          <button type="button" onclick="addItem()" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            İlk Kalemi Ekle
          </button>
        </div>

        <div id="totalSection" class="hidden mt-6 pt-6 border-t border-gray-200">
          <div class="flex justify-end">
            <div class="w-full md:w-1/3">
              <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex justify-between items-center text-lg font-semibold text-gray-900">
                  <span>Genel Toplam:</span>
                  <span id="grandTotal">0.00 <?= htmlspecialchars($cur ?? 'TRY', ENT_QUOTES, 'UTF-8') ?></span>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- Actions -->
      <div class="flex flex-col sm:flex-row gap-4 justify-end">
        <button type="button" onclick="goBack()" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">İptal</button>
        <button type="submit" id="submitBtn" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center">
          <span id="submitText">Teklif Oluştur</span>
          <svg id="submitSpinner" class="hidden loading-spinner w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        </button>
      </div>
    </form>
  </main>

  <script>
    // Utils
    function isUUID(v){ return /^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i.test(String(v||'')); }
    function showMessage(msg,type){
      document.querySelectorAll('.message-toast').forEach(e=>e.remove());
      const d=document.createElement('div');
      d.className=`message-toast fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 ${type==='success'?'bg-green-600 text-white':'bg-red-600 text-white'}`;
      d.textContent=msg; document.body.appendChild(d); setTimeout(()=>d.remove(),5000);
    }
    function goBack(){ if(confirm('Değişiklikler kaydedilmeyecek. Çıkmak istiyor musunuz?')) location.href='/is-ortaklar-paneli/bayi/index.php?page=teklifler'; }

    // Tarih min + default +30 gün (dolu değilse)
    function enforceDateMin(){
      const el=document.getElementById('validity_date');
      const now=new Date();
      const today=new Date(now.getTime()-now.getTimezoneOffset()*60000).toISOString().split('T')[0];
      el.setAttribute('min', today);
      if(!el.value){ const d=new Date(now); d.setDate(d.getDate()+30);
        el.value=new Date(d.getTime()-d.getTimezoneOffset()*60000).toISOString().split('T')[0];
      }
      el.addEventListener('change',()=>{ if(el.value<today) el.value=today; });
    }

    // Items
    let itemCounter=0;
    const products = window.PRODUCTS || [];

    function addItem(pref){
      if(!products.length){ showMessage('Ürün listesi yüklenemedi. Önce ürün tanımlayın.', 'error'); return; }
      itemCounter++;
      const itemId=`item_${itemCounter}`;
      const opts = products.map(p=>`<option value="${p.id}" data-price="${p.price??0}">${p.name}</option>`).join('');
      const html = `
      <div class="item-row border border-gray-200 rounded-lg p-4" data-item-id="${itemId}">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
          <div class="md:col-span-4">
            <label class="block md:hidden text-sm font-medium text-gray-700 mb-1">Ürün</label>
            <select name="product_id" required class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg" onchange="updateItemPrice('${itemId}')">
              <option value="">Ürün seçiniz</option>${opts}
            </select>
          </div>
          <div class="md:col-span-2">
            <label class="block md:hidden text-sm font-medium text-gray-700 mb-1">Miktar</label>
            <input type="number" name="qty" min="1" step="1" value="1" class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg" oninput="calculateItemTotal('${itemId}')">
          </div>
          <div class="md:col-span-2">
            <label class="block md:hidden text-sm font-medium text-gray-700 mb-1">Birim Fiyat</label>
            <input type="number" name="unit_price" min="0" step="0.01" class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg" oninput="calculateItemTotal('${itemId}')">
          </div>
          <div class="md:col-span-2">
            <label class="block md:hidden text-sm font-medium text-gray-700 mb-1">İndirim</label>
            <input type="number" name="discount" min="0" step="0.01" value="0" class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg" oninput="calculateItemTotal('${itemId}')">
          </div>
          <div class="md:col-span-1">
            <label class="block md:hidden text-sm font-medium text-gray-700 mb-1">Toplam</label>
            <div class="px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg">
              <span class="item-total font-medium">0.00</span>
            </div>
          </div>
          <div class="md:col-span-1">
            <label class="block md:hidden text-sm font-medium text-gray-700 mb-1">İşlem</label>
            <button type="button" class="w-full md:w-auto px-3 py-2 text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg" onclick="removeItem('${itemId}')">Sil</button>
          </div>
        </div>
      </div>`;
      const wrap=document.getElementById('itemsContainer');
      wrap.insertAdjacentHTML('beforeend', html);

      // Prefill (düzenleme)
      if(pref){
        const row=document.querySelector(`[data-item-id="${itemId}"]`);
        const sel=row.querySelector('select[name="product_id"]');
        const qty=row.querySelector('input[name="qty"]');
        const unit=row.querySelector('input[name="unit_price"]');
        const disc=row.querySelector('input[name="discount"]');
        if (pref.product_id) { sel.value=pref.product_id; }
        if (pref.qty)        { qty.value=pref.qty; }
        if (typeof pref.unit_price!=='undefined') { unit.value=pref.unit_price; }
        if (typeof pref.discount!=='undefined')   { disc.value=pref.discount; }
        // fiyatı ürün kartından doldur
        updateItemPrice(itemId);
        // toplamı güncelle
        calculateItemTotal(itemId);
      }

      updateDisplay();
    }
    function removeItem(id){ const el=document.querySelector(`[data-item-id="${id}"]`); if(!el) return; el.classList.add('removing'); setTimeout(()=>{ el.remove(); updateDisplay(); calculateGrandTotal(); }, 220); }
    function updateItemPrice(id){
      const el=document.querySelector(`[data-item-id="${id}"]`);
      const sel=el.querySelector('select[name="product_id"]');
      const unit=el.querySelector('input[name="unit_price"]');
      const opt=sel.options[sel.selectedIndex];
      if(opt && opt.dataset.price) unit.value=opt.dataset.price;
      calculateItemTotal(id);
    }
    function calculateItemTotal(id){
      const el=document.querySelector(`[data-item-id="${id}"]`);
      const qty = parseFloat(el.querySelector('input[name="qty"]').value)  || 0;
      const unit= parseFloat(el.querySelector('input[name="unit_price"]').value) || 0;
      const disc= parseFloat(el.querySelector('input[name="discount"]').value) || 0;
      const tot = Math.max(0, qty*unit - disc);
      el.querySelector('.item-total').textContent=tot.toFixed(2);
      calculateGrandTotal();
    }
    function calculateGrandTotal(){ let g=0; document.querySelectorAll('.item-total').forEach(t=>g+=parseFloat(t.textContent)||0);
      const cur=document.getElementById('currency').value; document.getElementById('grandTotal').textContent=`${g.toFixed(2)} ${cur}`; }
    function updateDisplay(){
      const hasItems=document.getElementById('itemsContainer').children.length>0;
      document.getElementById('noItemsMessage').classList.toggle('hidden', hasItems);
      document.getElementById('totalSection').classList.toggle('hidden', !hasItems);
      if(hasItems) calculateGrandTotal();
    }

    document.getElementById('currency').addEventListener('change', calculateGrandTotal);

    // SUBMIT
    document.getElementById('quoteForm').addEventListener('submit', async (e)=>{
      e.preventDefault();
      const btn=document.getElementById('submitBtn'), txt=document.getElementById('submitText'), sp=document.getElementById('submitSpinner');
      btn.disabled=true; txt.textContent='Oluşturuluyor...'; sp.classList.remove('hidden');
      try{
        const customerId=document.getElementById('customer_id').value;
        const currency=document.getElementById('currency').value;
        const validity=document.getElementById('validity_date').value || null;
        const notes=(document.getElementById('notes')?.value||'').trim()||null;
        if(!customerId) throw new Error('Müşteri seçimi zorunludur');

        const items=[]; let badUUID=false;
        document.querySelectorAll('.item-row').forEach(row=>{
          const pid=row.querySelector('select[name="product_id"]').value;
          const qty=parseFloat(row.querySelector('input[name="qty"]').value)||0;
          const unit=parseFloat(row.querySelector('input[name="unit_price"]').value)||0;
          const discount=parseFloat(row.querySelector('input[name="discount"]').value)||0;
          if(pid){
            if(!isUUID(pid)) { badUUID=true; return; }        // UUID kontrolü
            if(qty>0 && unit>=0) items.push({product_id:pid, qty:Number(qty), unit_price:Number(unit), discount:Number(discount), meta_params:null});
          }
        });
        if(badUUID) throw new Error('Seçtiğiniz ürünlerden birinin ID’si geçersiz.');

        const payload={ customer_id:String(customerId), currency, validity_date:validity, notes, items };
        const res=await fetch('/is-ortaklar-paneli/bayi/teklif-olustur.php?action=create',{ method:'POST', headers:{'Content-Type':'application/json','Cache-Control':'no-cache'}, body:JSON.stringify(payload), cache:'no-store' });
        const data=await res.json().catch(()=>({}));
        if(!res.ok){ const detail=data?.detail||data?.error||('HTTP '+res.status); throw new Error(detail); }

        showMessage('Teklif oluşturuldu'+(data.id?` (#${data.id})`:'') ,'success');
        setTimeout(()=>{ try{ if(window.parent) parent.postMessage({type:'quotes-reload'},'*'); }catch{} location.replace('/is-ortaklar-paneli/bayi/index.php?page=teklifler&ts='+Date.now()); },700);
      }catch(err){ console.error(err); showMessage('Teklif oluşturulamadı: '+(err.message||'Bilinmeyen hata'),'error'); }
      finally{ btn.disabled=false; txt.textContent='Teklif Oluştur'; sp.classList.add('hidden'); }
    });

    // Init
    window.addEventListener('load', ()=>{
      enforceDateMin();
      // Düzenleme modunda kalemleri doldur
      (window.PREFILL?.items||[]).forEach(it=> addItem({
        product_id: it.product_id || it.product?.id || '',
        qty: it.qty || 1,
        unit_price: typeof it.unit_price!=='undefined' ? it.unit_price : (it.price||0),
        discount: typeof it.discount!=='undefined' ? it.discount : 0
      }));
      updateDisplay();
    });
  </script>
</body>
</html>
