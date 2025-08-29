<?php
// file: /is-ortaklar-paneli/bayi/musterilerim.php

require_once __DIR__ . '/_boot.php';
require_bayi_role();
require_once __DIR__ . '/../config/config.php';

/* ========= Embed tespiti =========
   - Doğrudan açılırsa (bu dosya script olarak çalışıyorsa) embed=false
   - index.php içinden include edilirse embed=true (redirect olmaz)
*/
$IS_INCLUDED = (isset($_SERVER['SCRIPT_FILENAME'])
  && realpath(__FILE__) !== false
  && realpath($_SERVER['SCRIPT_FILENAME']) !== realpath(__FILE__));
$EMBED = $IS_INCLUDED || (isset($_GET['embed']) && $_GET['embed'] === '1');

/* Doğrudan erişimde merkezi layout'a yönlendir */
if (!$EMBED) {
  header('Location: /is-ortaklar-paneli/bayi/index.php?page=musterilerim');
  exit;
}

/* ========= API config ========= */
if (!defined('API_BASE')) define('API_BASE', 'http://34.44.194.247:3001/api/auth');
if (!defined('API_ROOT')) define('API_ROOT', preg_replace('~/auth/?$~i','', rtrim(API_BASE,'/')));
define('API_CUSTOMERS', rtrim(API_ROOT,'/').'/customers');

/* ========= Helpers ========= */
if (!function_exists('http_get_json_url')) {
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
}
if (!function_exists('partner_bearer')) {
  function partner_bearer(): string {
    foreach ([
      $_SESSION['partnerAccessToken'] ?? null,
      $_SESSION['bayiAccessToken']    ?? null,
      $_SESSION['accessToken']        ?? null,
    ] as $t) {
      if ($t && is_string($t)) return (stripos($t,'Bearer ')===0) ? $t : ('Bearer '.$t);
    }
    return '';
  }
}
if (!function_exists('pick')) {
  function pick(array $arr, array $keys, $fallback=null){
    foreach ($keys as $k) if (isset($arr[$k]) && $arr[$k] !== '') return $arr[$k];
    return $fallback;
  }
}

/* ========= Data fetch ========= */
$apiError = null;
$items    = [];
try {
  $bearer = partner_bearer();
  if ($bearer === '') throw new RuntimeException('Token bulunamadı. Lütfen bayi hesabıyla tekrar giriş yapın.');
  $data = http_get_json_url(API_CUSTOMERS, ['Accept: application/json', 'Authorization: '.$bearer]);
  $items = $data['items'] ?? [];
} catch (Throwable $e) {
  $apiError = $e->getMessage();
}

$customers = [];
foreach ($items as $it){
  $name = pick($it, ['name','title','full_name','company_name'], '—');
  $customers[] = [
    'id'       => (string)($it['id'] ?? ''),
    'name'     => $name,
    'company'  => pick($it, ['company','company_name','title'], $name),
    'email'    => pick($it, ['email','contact_email'], '—'),
    'phone'    => pick($it, ['phone','gsm','contact_phone'], '—'),
    'city'     => pick($it, ['city','il','sehir'], '—'),
    'status'   => strtolower(pick($it, ['status'], 'pending')),
    'joinDate' => pick($it, ['created_at','createdAt','created'], null),
  ];
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Müşterilerim</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .card-shadow { box-shadow: 0 4px 6px -1px rgba(0,0,0,.08), 0 2px 4px -1px rgba(0,0,0,.04); }
    .customer-card { transition: transform .2s ease, box-shadow .2s ease; }
    .customer-card:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0,0,0,.1), 0 4px 6px -2px rgba(0,0,0,.05); }
    .search-input:focus { box-shadow: 0 0 0 3px rgba(59,130,246,.12); }
    .status-active { background:#dcfce7; color:#166534; }
    .status-inactive { background:#fee2e2; color:#991b1b; }
    .status-pending { background:#fef3c7; color:#92400e; }
  </style>
</head>

<body class="bg-transparent">
  <!-- Tek beyaz kart (navbar yok!) -->
  <section class="max-w-7xl mx-auto my-6 rounded-2xl bg-white ring-1 ring-gray-100 card-shadow overflow-hidden">
    <div class="p-4 sm:p-6 lg:p-8">
      <div class="mb-6">
        <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">
          <div class="flex-1 w-full sm:max-w-md">
            <div class="relative">
              <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
              <input id="searchInput" type="text" placeholder="Müşteri ara..."
                     class="search-input w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                     onkeyup="searchCustomers()">
            </div>
          </div>
          <div class="flex items-center gap-3">
            <select id="statusFilter" onchange="filterByStatus()"
                    class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
              <option value="">Tüm Durumlar</option>
              <option value="active">Aktif</option>
              <option value="inactive">Pasif</option>
              <option value="pending">Beklemede</option>
            </select>
            <span class="text-sm text-gray-600" id="customerCount">0 müşteri</span>
          </div>
        </div>
        <?php if ($apiError): ?>
          <p class="mt-3 text-sm text-red-600">API Hatası: <?= htmlspecialchars($apiError, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="customerGrid"></div>

      <div id="emptyState" class="hidden text-center py-12">
        <svg class="mx-auto w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-3-3h-1m-1-3.05A2.5 2.5 0 1015 8m-3 7H6a3 3 0 00-3 3v2h5M9 12a2.5 2.5 0 100-5 2.5 2.5 0 000 5z"/>
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Müşteri bulunamadı</h3>
        <p class="text-gray-500">Arama kriterlerinize uygun müşteri bulunamadı.</p>
      </div>
    </div>
  </section>

  <script>
  const customers = <?php echo json_encode($customers, JSON_UNESCAPED_UNICODE); ?>;
  let filteredCustomers = [...customers];

  function initializePage(){ renderCustomers(); updateCustomerCount(); postHeight(); }
  function getInitials(name){ return String(name||'').trim().split(/\s+/).map(w=>w[0]||'').join('').toUpperCase().slice(0,2) || '--'; }
  function getStatusClass(s){ s=(s||'').toLowerCase(); if(s==='active') return 'status-active'; if(s==='inactive'||s==='passive') return 'status-inactive'; return 'status-pending'; }
  function getStatusText(s){ s=(s||'').toLowerCase(); if(s==='active') return 'Aktif'; if(s==='inactive'||s==='passive') return 'Pasif'; if(s==='pending') return 'Beklemede'; return 'Bilinmiyor'; }
  function formatDate(iso){ if(!iso) return '-'; const d=new Date(iso); return isNaN(d)?'-':d.toLocaleDateString('tr-TR',{year:'numeric',month:'short',day:'numeric'}); }

  function renderCustomers(){
    const grid = document.getElementById('customerGrid');
    const empty = document.getElementById('emptyState');
    if (!filteredCustomers.length){
      grid.innerHTML = ''; empty.classList.remove('hidden'); postHeight(); return;
    }
    empty.classList.add('hidden');
    grid.innerHTML = filteredCustomers.map(c=>`
      <div class="customer-card bg-white rounded-lg ring-1 ring-gray-100 card-shadow p-6" data-id="${c.id}">
        <div class="flex items-start justify-between mb-4">
          <div class="flex items-center">
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
              <span class="text-blue-600 font-semibold text-lg">${getInitials(c.name)}</span>
            </div>
            <div>
              <h3 class="font-semibold text-gray-900">${c.name||'—'}</h3>
              <p class="text-sm text-gray-600">${c.company||'—'}</p>
            </div>
          </div>
          <span class="px-2 py-1 rounded-full text-xs font-medium ${getStatusClass(c.status)}">${getStatusText(c.status)}</span>
        </div>
        <div class="space-y-2 mb-4">
          <div class="text-sm text-gray-600">${c.email||'—'}</div>
          <div class="text-sm text-gray-600">${c.phone||'—'}</div>
          <div class="text-sm text-gray-600">${c.city||'—'}</div>
        </div>
        <div class="flex items-center justify-between pt-4 border-t border-gray-100">
          <span class="text-xs text-gray-500">Katılım: ${formatDate(c.joinDate)}</span>
          <button onclick="viewCustomer('${c.id}')" class="text-blue-600 hover:text-blue-700 text-sm font-medium">Detaylar →</button>
        </div>
      </div>`).join('');
    postHeight();
  }

  function searchCustomers(){
    const q  = (document.getElementById('searchInput').value||'').toLowerCase();
    const st = document.getElementById('statusFilter').value;
    filteredCustomers = customers.filter(c=>{
      const hay = [c.name,c.company,c.email,c.city].map(x=>String(x||'').toLowerCase()).join(' ');
      const okQ = hay.includes(q);
      const okS = !st || (String(c.status||'').toLowerCase()===st);
      return okQ && okS;
    });
    renderCustomers(); updateCustomerCount();
  }
  function filterByStatus(){ searchCustomers(); }
  function updateCustomerCount(){ document.getElementById('customerCount').textContent = `${filteredCustomers.length} müşteri`; }
  function viewCustomer(id){ alert(`Müşteri detayı açılacak (id: ${id||'-'})`); }

  // Parent yüksekliği (index.php içindeki içerik alanını doğru uzatmak için)
  function postHeight(){
    try {
      const h = Math.max(document.body.scrollHeight, document.documentElement.scrollHeight);
      parent.postMessage({ type: 'musterilerim-height', height: h }, '*');
    } catch(_) {}
  }
  new ResizeObserver(postHeight).observe(document.body);
  window.addEventListener('load', initializePage);

  // Parent'tan yeniden yükleme isteği (opsiyonel)
  window.addEventListener('message', (e)=>{
    if (e?.data?.type === 'customers-reload') location.reload();
  });
  </script>
</body>
</html>
