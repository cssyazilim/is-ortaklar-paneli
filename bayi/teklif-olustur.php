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
    $partnerId = resolve_partner_id($bearer); // X-Partner-Id opsiyonel (varsa ekliyoruz)

    $raw = file_get_contents('php://input');
    if ($raw === '' || $raw === false) { http_response_code(400); echo json_encode(['error'=>'empty_body']); exit; }

    $headers = ['Accept: application/json','Content-Type: application/json','Authorization: '.$bearer];
    if ($partnerId !== '') $headers[] = 'X-Partner-Id: '.$partnerId;

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

/* ==================== Sayfa verileri ==================== */
$bearer = partner_bearer();
$partnerId = resolve_partner_id($bearer);

$customerOptions = [];
$productOptions  = [];
$customersError  = null;
$productsError   = null;
$quote           = null;

// Düzenleme modu
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

 // Ürünler  ─ /api/products hem düz dizi hem {items:[...]} dönebilir
$pData  = http_get_json_url(rtrim(API_ROOT,'/').'/products?limit=200', $headers);
$plist  = [];
if (is_array($pData)) {
  if (isset($pData['items']) && is_array($pData['items'])) {
    $plist = $pData['items'];
  } else {
    // düz dizi mi?
    $isList = !empty($pData) && array_keys($pData) === range(0, count($pData)-1);
    if ($isList) $plist = $pData;
  }
}
foreach ($plist as $p) {
  $pid   = (string)($p['id'] ?? '');
  $pname = $p['name'] ?? $p['code'] ?? $p['title'] ?? 'Ürün';
  // API’nizde price alanı yok; 0 verelim (kullanıcı isterse değiştirir)
  $price = isset($p['price']) ? (float)$p['price'] : 0.0;
  if ($pid !== '') $productOptions[] = ['id'=>$pid,'name'=>$pname,'price'=>$price];
}
} catch (Throwable $e) {
  if (!$customerOptions) $customersError = $e->getMessage();
  if (!$productOptions)  $productsError  = $e->getMessage();
}

$cur = $quote['currency'] ?? 'TRY';
$tz = new DateTimeZone('Europe/Istanbul');           // kendi TZ’n varsa onu kullan
$todayYmd = (new DateTime('today', $tz))->format('Y-m-d');
$validityValue = iso_to_ymd($quote['validity_date'] ?? null) ?: $todayYmd;
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
    .loading-spinner { animation: spin 1s linear infinite; }
    @keyframes spin { from{transform:rotate(0)} to{transform:rotate(360deg)} }
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
         <select id="customer_id" name="customer_id"
  class="form-input w-full h-11 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
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


        <!-- Geçerlilik tarihi -->
        <div>
          <label for="validity_date" class="block text-sm font-medium text-gray-700 mb-2">Geçerlilik Tarihi</label>
         <input
          type="date"
          id="validity_date"
          name="validity_date"
          min="<?= $todayYmd ?>"                              
          value="<?= htmlspecialchars($validityValue, ENT_QUOTES, 'UTF-8') ?>"  
          class="form-input w-full h-11 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
     </div>
      </div>

      <!-- Notlar -->
      <div class="mt-6">
        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notlar</label>
        <textarea id="notes" name="notes" rows="3" placeholder="Teklif ile ilgili notlarınızı yazın..." class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none resize-none"><?= htmlspecialchars($quote['notes'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
      </div>
    </div>

    <!-- Kalemler (DB'den gelecek) -->
    <div class="bg-white rounded-lg card-shadow p-6">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-semibold text-gray-900 flex items-center">
          <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
          Teklif Kalemleri
        </h2>
       <button type="button" onclick="openProductPicker()" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">

         <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
          Ürünleri Getir
        </button>
      </div>

      <!-- Head -->
      <div class="hidden md:grid grid-cols-12 gap-4 mb-4 p-3 bg-gray-50 rounded-lg text-sm font-medium text-gray-700">
        <div class="col-span-6 md:col-span-4">Ürün</div>
        <div class="col-span-2">Miktar</div>
        <div class="col-span-2">Birim Fiyat</div>
        <div class="col-span-2">İndirim</div>
        <div class="col-span-2 md:col-span-1">Toplam</div>
        <div class="hidden md:block md:col-span-1">&nbsp;</div>
      </div>

      <div id="itemsContainer" class="space-y-4"></div>

      <div id="noItemsMessage" class="text-center py-8 border-2 border-dashed border-gray-300 rounded-lg">
        <svg class="mx-auto w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
        <p class="text-gray-500 mb-4">Kalemler veritabanından yüklenecek.</p>
        <button type="button" onclick="loadItemsFromDB()" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
          Ürünleri Getir
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
  <!-- Ürün Seçim Penceresi -->
<div id="productPicker" class="hidden fixed inset-0 z-50">
  <!-- overlay -->
  <div class="absolute inset-0 bg-black/40" onclick="closeProductPicker()"></div>

  <!-- modal -->
  <div class="relative mx-auto mt-20 w-11/12 max-w-3xl">
    <div class="bg-white rounded-xl shadow-xl overflow-hidden">
      <div class="px-5 py-4 border-b flex items-center justify-between">
        <h3 class="text-lg font-semibold">Ürün Seç</h3>
        <button class="text-gray-500 hover:text-gray-700" onclick="closeProductPicker()">✕</button>
      </div>

      <div class="px-5 py-3 flex items-center gap-3">
        <input id="pickerSearch" type="text" placeholder="Ürün adı / kodu ara..." class="w-full px-3 py-2 border rounded-lg outline-none focus:ring-2 focus:ring-indigo-500" oninput="filterPickerList()">
      </div>

      <div class="px-5 pb-4 max-h-[420px] overflow-auto">
        <table class="w-full text-sm">
          <thead class="text-left text-gray-600">
            <tr>
              <th class="w-10"></th>
              <th>Ürün</th>
              <th class="w-24">Miktar</th>
              <th class="w-32">Birim Fiyat</th>
            </tr>
          </thead>
          <tbody id="pickerList"></tbody>
        </table>

        <p id="pickerEmpty" class="hidden text-gray-500 text-sm py-6 text-center">Ürün bulunamadı.</p>
      </div>

      <div class="px-5 py-4 border-t flex justify-end gap-3">
        <button class="px-4 py-2 rounded-lg border" onclick="closeProductPicker()">Vazgeç</button>
        <button class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700" onclick="addSelectedProducts()">Seçilenleri Ekle</button>
      </div>
    </div>
  </div>
</div>

</main>

<script>
    function todayISO(){
    const d = new Date();
    d.setHours(0,0,0,0);
    return new Date(d.getTime() - d.getTimezoneOffset()*60000)
      .toISOString().slice(0,10);
  }

  document.addEventListener('DOMContentLoaded', () => {
    const el = document.getElementById('validity_date');
    if (!el) return;

    // HTML'de min zaten verili; yine de garanti:
    if (!el.min) el.min = todayISO();

    // Takvim açılmadan önce min'i güncel tut
    ['focus','click','pointerdown','mousedown','touchstart','keydown']
      .forEach(evt => el.addEventListener(evt, () => { el.min = todayISO(); }, {capture:true}));

    // Kullanıcı eski tarih yazarsa düzelt
    ['input','change','blur'].forEach(evt =>
      el.addEventListener(evt, () => {
        if (el.value && el.value < el.min) el.value = el.min;
      })
    );
  });
  // ===== Utils =====
  function isUUID(v){ return /^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i.test(String(v||'')); }
  function showMessage(msg,type){
    document.querySelectorAll('.message-toast').forEach(e=>e.remove());
    const d=document.createElement('div');
    d.className = `message-toast fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 ${type==='success'?'bg-green-600 text-white':'bg-red-600 text-white'}`;
    d.textContent=msg;
    document.body.appendChild(d);
    setTimeout(()=>d.remove(),5000);
  }
  function goBack(){ if(confirm('Değişiklikler kaydedilmeyecek. Çıkmak istiyor musunuz?')) location.href='/is-ortaklar-paneli/bayi/index.php?page=teklifler'; }

  // Tarih min + default +30 gün (dolu değilse)
  function enforceDateMin(){
    const el=document.getElementById('validity_date');
    const now=new Date();
    const today=new Date(now.getTime()-now.getTimezoneOffset()*60000).toISOString().split('T')[0];
    el.setAttribute('min', today);
    if(!el.value){
      const d=new Date(now); d.setDate(d.getDate()+30);
      el.value=new Date(d.getTime()-d.getTimezoneOffset()*60000).toISOString().split('T')[0];
    }
    el.addEventListener('change',()=>{ if(el.value<today) el.value=today; });
  }

  // ===== Para birimi (kalemlerden otomatik) =====
  let currentCurrency = (window.PREFILL && window.PREFILL.currency) || '<?= htmlspecialchars($cur ?? 'TRY', ENT_QUOTES, 'UTF-8') ?>';
  function updateCurrencyDisplay(){
    const el = document.getElementById('currencyDisplay');
    if (el) el.value = currentCurrency || '—';
    const hid = document.getElementById('currency');
    if (hid) hid.value = currentCurrency || '';
  }
  function guessCurrencyFromItems(items){
    let cur = null;
    for (const raw of (items||[])){
      const c = raw.currency || raw.unit_currency || raw.price_currency || (raw.product && raw.product.currency) || null;
      if (!cur && c) cur = c;
      else if (cur && c && c !== cur){ showMessage('Kalemlerde birden fazla para birimi var. İlk bulunan kullanılacak: '+cur, 'error'); break; }
    }
    return cur || currentCurrency || '<?= htmlspecialchars($cur ?? 'TRY', ENT_QUOTES, 'UTF-8') ?>';
  }

  // ===== Kalemler (read-only) =====
  let currentItems = [];

  function normalizeItem(it){
    const pid = it.product_id || (it.product && it.product.id) || '';
    const pname = (it.product && it.product.name) || it.product_name || it.name || 'Ürün';
    const qty = Number(it.qty || 1);
    const unit = Number((it.unit_price != null ? it.unit_price : (it.price || 0)));
    const disc = Number(it.discount || 0);
    return { product_id: String(pid), product_name: String(pname), qty: qty, unit_price: unit, discount: disc };
  }

  function setItems(items){
    currentItems = (items||[]).map(normalizeItem);
    currentCurrency = guessCurrencyFromItems(items);
    updateCurrencyDisplay();
    renderItems();
    calculateGrandTotal();
    updateDisplay();
  }

  function renderItems(){
  const wrap = document.getElementById('itemsContainer');
  wrap.innerHTML = '';
  currentItems.forEach((it, idx)=>{
    const total = Math.max(0, (it.qty*it.unit_price) - it.discount);
    const row = document.createElement('div');
    row.className = 'item-row border border-gray-200 rounded-lg p-4';
    row.innerHTML = `
      <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
        <div class="md:col-span-4">
          <label class="block md:hidden text-sm font-medium text-gray-700 mb-1">Ürün</label>
          <div class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-900">
            <div class="font-medium truncate">${escapeHtml(it.product_name)}</div>
            <div class="text-xs text-gray-500 break-all">${escapeHtml(it.product_id)}</div>
          </div>
        </div>

        <div class="md:col-span-2">
          <label class="block md:hidden text-sm font-medium text-gray-700 mb-1">Miktar</label>
          <input type="number" min="1" step="1"
                 class="w-full px-3 py-2 border rounded-lg"
                 value="${Number(it.qty).toFixed(0)}"
                 data-idx="${idx}" oninput="updateItemField(this,'qty')">
        </div>

        <div class="md:col-span-2">
          <label class="block md:hidden text-sm font-medium text-gray-700 mb-1">Birim Fiyat</label>
          <input type="number" step="0.01" min="0"
                 class="w-full px-3 py-2 border rounded-lg"
                 value="${Number(it.unit_price).toFixed(2)}"
                 data-idx="${idx}" oninput="updateItemField(this,'unit_price')">
        </div>

        <div class="md:col-span-2">
          <label class="block md:hidden text-sm font-medium text-gray-700 mb-1">İndirim</label>
          <input type="number" step="0.01" min="0"
                 class="w-full px-3 py-2 border rounded-lg"
                 value="${Number(it.discount).toFixed(2)}"
                 data-idx="${idx}" oninput="updateItemField(this,'discount')">
        </div>

        <div class="md:col-span-1">
          <label class="block md:hidden text-sm font-medium text-gray-700 mb-1">Toplam</label>
          <div class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-900 font-semibold">
            <span class="item-total" id="itemTotal_${idx}">${total.toFixed(2)}</span>
          </div>
        </div>

        <div class="md:col-span-1 flex items-center justify-end">
          <button type="button" class="px-3 py-2 text-red-600 hover:text-red-700" onclick="removeItem(${idx})">Sil</button>
        </div>
      </div>
    `;
    wrap.appendChild(row);
  });
}
function updateItemField(el, field){
  const i = Number(el.dataset.idx);
  if (Number.isNaN(i) || !currentItems[i]) return;
  let v = field === 'qty' ? parseInt(el.value || '0', 10) : parseFloat(el.value || '0');
  if (!Number.isFinite(v) || v < 0) v = 0;
  if (field === 'qty' && v < 1) v = 1;
  currentItems[i][field] = v;

  const total = Math.max(0, (currentItems[i].qty*currentItems[i].unit_price) - currentItems[i].discount);
  const tgt = document.getElementById(`itemTotal_${i}`);
  if (tgt) tgt.textContent = total.toFixed(2);
  calculateGrandTotal();
}

function openProductPicker(){
  const listEl = document.getElementById('pickerList');
  const emptyEl = document.getElementById('pickerEmpty');
  document.getElementById('pickerSearch').value = '';

  const prods = Array.isArray(window.PRODUCTS) ? window.PRODUCTS : [];
  listEl.innerHTML = '';
  prods.forEach(p=>{
    const id = String(p.id||'');
    const name = p.name || p.title || p.code || 'Ürün';
    const price = Number(p.price || 0);

    const tr = document.createElement('tr');
    tr.className = 'border-t';
    tr.dataset.name = (name + ' ' + (p.code||'')).toLowerCase();

    tr.innerHTML = `
      <td class="py-2 align-middle">
        <input type="checkbox" class="w-4 h-4" data-id="${id}">
      </td>
      <td class="py-2">
        <div class="font-medium">${escapeHtml(name)}</div>
        <div class="text-xs text-gray-500 break-all">${escapeHtml(id)}</div>
      </td>
      <td class="py-2">
        <input type="number" min="1" step="1" value="1"
               class="w-20 px-2 py-1 border rounded"
               data-for="${id}" data-field="qty">
      </td>
      <td class="py-2">
        <input type="number" min="0" step="0.01" value="${price.toFixed(2)}"
               class="w-28 px-2 py-1 border rounded"
               data-for="${id}" data-field="price">
      </td>
    `;
    listEl.appendChild(tr);
  });

  emptyEl.classList.toggle('hidden', prods.length>0);
  document.getElementById('productPicker').classList.remove('hidden');
}

function closeProductPicker(){
  document.getElementById('productPicker').classList.add('hidden');
}

function filterPickerList(){
  const q = document.getElementById('pickerSearch').value.trim().toLowerCase();
  const rows = Array.from(document.querySelectorAll('#pickerList tr'));
  let visible = 0;
  rows.forEach(r=>{
    const hit = !q || (r.dataset.name || '').includes(q);
    r.classList.toggle('hidden', !hit);
    if (hit) visible++;
  });
  document.getElementById('pickerEmpty').classList.toggle('hidden', visible>0);
}

function addSelectedProducts(){
  const rows = Array.from(document.querySelectorAll('#pickerList tr'));
  const chosen = [];
  rows.forEach(r=>{
    const cb = r.querySelector('input[type="checkbox"]');
    if (!cb || !cb.checked) return;
    const id = cb.dataset.id;
    const name = r.querySelector('.font-medium')?.textContent || 'Ürün';
    const qty  = parseInt(r.querySelector('input[data-for="'+id+'"][data-field="qty"]')?.value || '1', 10);
    const price= parseFloat(r.querySelector('input[data-for="'+id+'"][data-field="price"]')?.value || '0');
    chosen.push({id, name, qty: Math.max(1, qty|0), unit_price: Math.max(0, +price), discount: 0});
  });

  if (!chosen.length){ showMessage('Ürün seçmediniz.', 'error'); return; }

  // mevcutlarla birleştir (aynı ürün varsa miktarı artır)
  chosen.forEach(c=>{
    const idx = currentItems.findIndex(x=>String(x.product_id)===String(c.id));
    if (idx>=0){
      currentItems[idx].qty = Number(currentItems[idx].qty||0) + c.qty;
      if (!currentItems[idx].product_name) currentItems[idx].product_name = c.name;
      if ((currentItems[idx].unit_price||0)===0 && c.unit_price>0) currentItems[idx].unit_price = c.unit_price;
    }else{
      currentItems.push({
        product_id: c.id,
        product_name: c.name,
        qty: c.qty,
        unit_price: c.unit_price,
        discount: 0
      });
    }
  });

  renderItems();
  updateDisplay();
  calculateGrandTotal();
  closeProductPicker();
  showMessage('Seçilen ürünler eklendi.', 'success');
}


function removeItem(idx){
  currentItems.splice(idx,1);
  renderItems();
  updateDisplay();
  calculateGrandTotal();
}


  function escapeHtml(s){
    return String(s==null?'':s)
      .replaceAll('&','&amp;')
      .replaceAll('<','&lt;')
      .replaceAll('>','&gt;')
      .replaceAll('"','&quot;')
      .replaceAll("'",'&#39;');
  }

  function calculateGrandTotal(){
    let g=0;
    currentItems.forEach(it=>{
      g += Math.max(0, (Number(it.qty)||0)*(Number(it.unit_price)||0) - (Number(it.discount)||0));
    });
    document.getElementById('grandTotal').textContent=`${g.toFixed(2)} ${currentCurrency}`;
  }

  function updateDisplay(){
    const hasItems = currentItems.length > 0;
    document.getElementById('noItemsMessage').classList.toggle('hidden', hasItems);
    document.getElementById('totalSection').classList.toggle('hidden', !hasItems);
    if(hasItems) calculateGrandTotal();
  }

   // ===== Ürünleri Getir (DUMMY ama gerçek ürün ID'leriyle) =====
  async function loadItemsFromDB(){
  try{
    const customerId = document.getElementById('customer_id').value;
    if(!customerId){ showMessage('Önce müşteri seçin.', 'error'); return; }

    const prods = Array.isArray(window.PRODUCTS) ? window.PRODUCTS : [];
    if (!prods.length){
      showMessage('Tanımlı ürün bulunamadı. Lütfen ürün ekleyin.', 'error');
      return;
    }

    // İlk 2-3 üründen örnek kalemler (FK için GERÇEK UUID kullanıyoruz)
    const pick = prods.slice(0, 3); // 1–3 arası ekleyebilir
    const items = pick.map((p, i) => ({
      product_id: p.id,
      product_name: p.name || p.code || 'Ürün',
      qty: (i + 1),                         // 1,2,3...
      unit_price: Number(p.price || 0),     // API fiyat döndürmüyor → 0
      discount: 0,
      price_currency: (window.PREFILL && window.PREFILL.currency) || 'TRY'
    }));

    setItems(items);
    showMessage('Ürünler eklendi.', 'success');
  }catch(err){
    console.error(err);
    showMessage('Kalemler yüklenemedi', 'error');
  }
}

  // ===== SUBMIT =====
  document.getElementById('quoteForm').addEventListener('submit', async (e)=>{
    e.preventDefault();
    const btn=document.getElementById('submitBtn'), txt=document.getElementById('submitText'), sp=document.getElementById('submitSpinner');
    btn.disabled=true; txt.textContent='Oluşturuluyor...'; sp.classList.remove('hidden');
    try{
      const customerId=document.getElementById('customer_id').value;
      const currency = currentCurrency;
      const validity=document.getElementById('validity_date').value || null;
      const notes=(document.getElementById('notes')?.value||'').trim()||null;
      if(!customerId) throw new Error('Müşteri seçimi zorunludur');
      if(currentItems.length===0) throw new Error('Kalem listesi boş. Lütfen "Ürünleri Getir" ile kalemleri yükleyin.');

      let badUUID=false;
      const items = currentItems.map(it=>{
        if(!isUUID(it.product_id)) badUUID=true;
        return {
          product_id: it.product_id,
          qty: Number(it.qty)||0,
          unit_price: Number(it.unit_price)||0,
          discount: Number(it.discount)||0,
          meta_params: null
        };
      });
      if(badUUID) throw new Error('Kalemlerden birinin ürün ID’si geçersiz.');

      const payload={ customer_id:String(customerId), currency, validity_date:validity, notes, items };

      const res=await fetch('/is-ortaklar-paneli/bayi/teklif-olustur.php?action=create',{
        method:'POST',
        headers:{ 'Content-Type':'application/json', 'Accept':'application/json' },
        credentials:'same-origin',
        body:JSON.stringify(payload),
        cache:'no-store'
      });
      const data=await res.json().catch(()=>({}));

      if(!res.ok){ const detail=data?.detail||data?.error||('HTTP '+res.status); throw new Error(detail); }

      showMessage('Teklif oluşturuldu'+(data.id?` (#${data.id})`:'') ,'success');
      setTimeout(()=>{ try{ if(window.parent) parent.postMessage({type:'quotes-reload'},'*'); }catch{} location.replace('/is-ortaklar-paneli/bayi/index.php?page=teklifler&ts='+Date.now()); },700);
    }catch(err){
      console.error(err);
      showMessage('Teklif oluşturulamadı: '+(err.message||'Bilinmeyen hata'),'error');
    } finally{
      btn.disabled=false; txt.textContent='Teklif Oluştur'; sp.classList.add('hidden');
    }
  });

  // Init
  window.addEventListener('load', ()=>{
    enforceDateMin();
    if (Array.isArray(window.PREFILL?.items) && window.PREFILL.items.length>0) {
      setItems(window.PREFILL.items);
    } else {
      updateDisplay();
      updateCurrencyDisplay();
      calculateGrandTotal();
    }
  });
</script>
</body>
</html>
