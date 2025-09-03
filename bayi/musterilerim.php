<?php
// file: /is-ortaklar-paneli/bayi/musterilerim.php

require_once __DIR__ . '/_boot.php';
require_bayi_role();
require_once __DIR__ . '/../config/config.php';

/* ========= Embed tespiti ========= */
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
    foreach ($keys as $k) {
      if (array_key_exists($k, $arr)) {
        $v = $arr[$k];
        // null, boş string ve sadece whitespace'leri boş say
        if ($v !== null) {
          if (is_string($v)) { $v = trim($v); }
          if ($v !== '') return $v;
        }
      }
    }
    return $fallback;
  }
}

/* "null" yazmak yerine uzun tire göster */
function dash($v){
  if ($v === null) return '—';
  if (is_string($v) && trim($v)==='') return '—';
  return $v;
}

/* null ise kelime olarak "null" döndür */
function show_or_null($v){
  return ($v === null || $v === '') ? 'null' : $v;
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

/* ========= Map to UI model ========= */
$customers = [];
foreach ($items as $it){
  $display = pick($it, ['title','name','company_name','full_name'], '—');
  $customers[] = [
    'id'         => (string)($it['id'] ?? ''),
    'name'       => $display,
    'company'    => pick($it, ['title','company','company_name'], $display),
    'email'      => array_key_exists('email',$it)   ? show_or_null($it['email'])   : 'null',
    'phone'      => array_key_exists('phone',$it)   ? show_or_null($it['phone'])   : 'null',
    'address'    => array_key_exists('address',$it) ? show_or_null($it['address']) : 'null',
    'type'       => $it['type'] ?? '—',
    'program'    => $it['program'] ?? '—',
    'userCount'  => (int)($it['user_count'] ?? 0),
    'status'     => strtolower($it['status'] ?? 'pending'),
    'createdAt'  => $it['created_at'] ?? null,
    'updatedAt'  => $it['updated_at'] ?? null,
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
    .card-shadow { box-shadow:0 4px 6px -1px rgba(0,0,0,.08), 0 2px 4px -1px rgba(0,0,0,.04); }
    .customer-card { transition:transform .2s ease, box-shadow .2s ease; }
    .customer-card:hover { transform:translateY(-2px); box-shadow:0 10px 15px -3px rgba(0,0,0,.1), 0 4px 6px -2px rgba(0,0,0,.05); }
    .search-input:focus { box-shadow:0 0 0 3px rgba(59,130,246,.12); }

    @media print {
      body * { visibility: hidden !important; }
      #printArea, #printArea * { visibility: visible !important; }
      #printArea {
        position: absolute; inset: 0; display: block !important; background: #fff;
      }
      #printTable { width: 100%; border-collapse: collapse; }
      #printTable th, #printTable td { border: 1px solid #ddd; padding: 8px; font-size: 12px; }
      #printTable th { background: #f3f4f6; text-align: left; }
    }
  </style>
</head>

<body class="bg-transparent">
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
          <div class="flex items-center gap-3 flex-wrap">
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

  <!-- Yazdırma alanı -->
  <div id="printArea" class="hidden p-8"></div>

  <script>
  const customers = <?php echo json_encode($customers, JSON_UNESCAPED_UNICODE); ?>;

   /* === API ve Yetki (detay çekmek için) === */
  const API_CUSTOMERS_URL = <?php echo json_encode(API_CUSTOMERS, JSON_UNESCAPED_SLASHES); ?>;
  const AUTH_BEARER       = <?php echo json_encode(partner_bearer()); ?>;
  window.__currentCustomerId = null;

  let filteredCustomers = [...customers];

  function initializePage(){ renderCustomers(); updateCustomerCount(); postHeight(); }
  function getInitials(name){ return String(name||'').trim().split(/\s+/).map(w=>w[0]||'').join('').toUpperCase().slice(0,2) || '--'; }

  function statusPillClass(s){
    s = (s || '').toLowerCase();
    if (s === 'active')  return 'inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700 border border-green-200';
    if (s === 'inactive' || s === 'passive') return 'inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700 border border-red-200';
    return 'inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700 border border-amber-200';
  }
  function getStatusText(s){
    s=(s||'').toLowerCase();
    if(s==='active') return 'Aktif';
    if(s==='inactive'||s==='passive') return 'Pasif';
    if(s==='pending') return 'Beklemede';
    return 'Bilinmiyor';
  }
  function formatDate(iso){
    if(!iso) return '-';
    const d=new Date(iso);
    return isNaN(d)?'-':d.toLocaleDateString('tr-TR',{year:'numeric',month:'short',day:'numeric'});
  }

  function renderCustomers(){
    const grid = document.getElementById('customerGrid');
    const empty = document.getElementById('emptyState');

    if (!filteredCustomers.length){
      grid.innerHTML = '';
      empty.classList.remove('hidden');
      postHeight();
      return;
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
          <span class="${statusPillClass(c.status)}">${getStatusText(c.status)}</span>
        </div>

        <div class="space-y-2 mb-4">
          <div class="flex items-center text-sm text-gray-600">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
            </svg>
            ${c.email}
          </div>
          <div class="flex items-center text-sm text-gray-600">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
            </svg>
            ${c.phone}
          </div>
          <div class="flex items-center text-sm text-gray-600">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            ${c.address}
          </div>

          <div class="text-xs text-gray-500">

            <span class="mr-3"><strong>Kullanıcı:</strong> ${c.userCount}</span>
          </div>
        </div>

        <div class="flex items-center justify-between pt-4 border-t border-gray-100">
          <span class="text-xs text-gray-500">Oluşturma: ${formatDate(c.createdAt)}</span>
          <button onclick="viewCustomer('${c.id}')" class="text-blue-600 hover:text-blue-700 text-sm font-medium">Detaylar →</button>
        </div>
      </div>`).join('');

    postHeight();
  }

  function searchCustomers(){
    const q  = (document.getElementById('searchInput').value||'').toLowerCase();
    const st = document.getElementById('statusFilter').value;
    filteredCustomers = customers.filter(c=>{
      const hay = [c.name,c.company,c.email,c.phone,c.address,c.program,c.type]
                    .map(x=>String(x||'').toLowerCase()).join(' ');
      const okQ = hay.includes(q);
      const okS = !st || (String(c.status||'').toLowerCase()===st);
      return okQ && okS;
    });
    renderCustomers(); updateCustomerCount();
  }
  function filterByStatus(){ searchCustomers(); }
  function updateCustomerCount(){ document.getElementById('customerCount').textContent = `${filteredCustomers.length} müşteri`; }
  async function viewCustomer(id){
    openCustomerModal();
    modalLoading();
     window.__currentCustomerId = id; // <— eklendi
    try{
      const res = await fetch(`${API_CUSTOMERS_URL}/${encodeURIComponent(id)}`, {
        headers: { 'Accept': 'application/json', 'Authorization': AUTH_BEARER }
      });
      if(!res.ok){
        const tx = await res.text().catch(()=> ''); 
        throw new Error(`Detay yüklenemedi (HTTP ${res.status}) ${tx||''}`);
      }
      const data = await res.json();
      renderCustomerModal(data);
      postHeight();
    }catch(err){
      document.getElementById('customerModalBody').innerHTML =
        `<div class="p-8 text-center">
           <div class="text-rose-600 font-semibold mb-2">Hata</div>
           <div class="text-gray-600 mb-4">${esc(err.message)}</div>
           <button class="px-4 py-2 rounded-lg bg-gray-800 text-white" onclick="closeCustomerModal()">Kapat</button>
         </div>`;
      postHeight();
    }
  }

  function editCustomer(){
  const id = window.__currentCustomerId;
  if(!id) return;
  const url = '/is-ortaklar-paneli/bayi/musteriKayit.php?id=' + encodeURIComponent(id);
  if (window.top && window.top !== window.self) {
    window.top.location.href = url; // iframe içindeyse üstte aç
  } else {
    window.location.href = url;
  }
}

function removeCustomerFromLists(id){
  const rm = (arr)=> {
    const i = arr.findIndex(x => String(x.id) === String(id));
    if (i >= 0) arr.splice(i,1);
  };
  rm(customers); rm(filteredCustomers);
  renderCustomers(); updateCustomerCount();
}

async function deleteCustomer(){
  const id = window.__currentCustomerId;
  if(!id) return;
  if(!confirm('Bu müşteriyi silmek istediğinize emin misiniz?')) return;

  try{
    const res = await fetch(`${API_CUSTOMERS_URL}/${encodeURIComponent(id)}`, {
      method: 'DELETE',
      headers: { 'Accept': 'application/json', 'Authorization': AUTH_BEARER }
    });
    if(!res.ok){
      const txt = await res.text().catch(()=> '');
      throw new Error(`Silinemedi (HTTP ${res.status}) ${txt||''}`);
    }
    alert('Müşteri silindi.');
    closeCustomerModal();
    removeCustomerFromLists(id);
  }catch(err){
    alert('Hata: ' + (err?.message || 'Silme işlemi başarısız.'));
  }
}


  // Parent yüksekliği
  function postHeight(){
    try {
      const h = Math.max(document.body.scrollHeight, document.documentElement.scrollHeight);
      parent.postMessage({ type: 'musterilerim-height', height: h }, '*');
    } catch(_) {}
  }
  new ResizeObserver(postHeight).observe(document.body);
  window.addEventListener('load', initializePage);

  // Parent'tan reload isteği
  window.addEventListener('message', (e)=>{
    if (e?.data?.type === 'customers-reload') location.reload();
  });

  /* ========= Toplu e-posta/telefon: kopyala + yazdır ========= */
  function _uniqueNonEmpty(arr) {
    return [...new Set(arr.map(x => String(x || '').trim()).filter(x => x && x !== 'null'))];
  }
  function _normalizePhone(p) { return String(p || '').replace(/[^\d+]/g, ''); }
  async function _copyToClipboard(text) {
    try { await navigator.clipboard.writeText(text); alert('Panoya kopyalandı.'); }
    catch {
      const ta = document.createElement('textarea'); ta.value = text; document.body.appendChild(ta);
      ta.select(); document.execCommand('copy'); document.body.removeChild(ta); alert('Panoya kopyalandı.');
    }
  }
  function copyEmails() {
    const emails = _uniqueNonEmpty(filteredCustomers.map(c => c.email));
    if (!emails.length) { alert('E-posta bulunamadı.'); return; }
    _copyToClipboard(emails.join(', '));
  }
  function copyPhones() {
    const phones = _uniqueNonEmpty(filteredCustomers.map(c => _normalizePhone(c.phone)));
    if (!phones.length) { alert('Telefon bulunamadı.'); return; }
    _copyToClipboard(phones.join(', '));
  }
  function _buildPrintArea() {
    const el = document.getElementById('printArea');
    const now = new Date().toLocaleString('tr-TR');
    const rows = filteredCustomers.map(c => `
      <tr>
        <td>${c.name || '—'}</td>
        <td>${c.company || '—'}</td>
        <td>${c.email}</td>
        <td>${c.phone}</td>
        <td>${c.address}</td>
        <td>${c.type || '—'}</td>
        <td>${c.program}</td>
        <td>${c.userCount}</td>
        <td>${getStatusText(c.status)}</td>
        <td>${formatDate(c.createdAt)}</td>
      </tr>
    `).join('');
    el.innerHTML = `
      <h1 style="font:600 18px system-ui, -apple-system, Segoe UI, Roboto">Müşteri İletişim Listesi</h1>
      <div style="margin:6px 0 14px;color:#6b7280;font:14px system-ui">Toplam: ${filteredCustomers.length} • Tarih: ${now}</div>
      <table id="printTable">
        <thead>
          <tr>
            <th>Görünen Ad</th>
            <th>Firma</th>
            <th>E-posta</th>
            <th>Telefon</th>
            <th>Adres</th>
            <th>Tip</th>
            <th>Program</th>
            <th>Kullanıcı</th>
            <th>Durum</th>
            <th>Oluşturma</th>
          </tr>
        </thead>
        <tbody>${rows}</tbody>
      </table>
    `;
  }
  function printContacts() {
    if (!filteredCustomers.length) { alert('Yazdırılacak müşteri yok.'); return; }
    _buildPrintArea();
    const area = document.getElementById('printArea');
    area.classList.remove('hidden');
    postHeight();
    setTimeout(() => {
      window.print();
      setTimeout(() => { area.classList.add('hidden'); postHeight(); }, 300);
    }, 50);
  }

    /* ====== Modal Helpers ====== */
  function esc(s){ return String(s ?? '—')
    .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
    .replace(/"/g,'&quot;').replace(/'/g,'&#39;'); }
  function pick(o, keys, fb='—'){ for(const k of keys){ const v=o?.[k]; if(v!==undefined && v!==null && v!=='') return v; } return fb; }
  function fmtMoney(n){ if(n===null||n===undefined||n==='') return '—'; const x=Number(n); if(Number.isNaN(x)) return esc(n); return x.toLocaleString('tr-TR',{maximumFractionDigits:2})+' TL'; }
  function fmtDateTime(iso){ if(!iso) return '—'; const d=new Date(iso); return isNaN(d)?'—':d.toLocaleString('tr-TR'); }
  function riskBadge(level){
    const l=String(level||'').toLowerCase();
    if(['low','düşük','dusuk'].includes(l))   return '<span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 border border-green-200">Düşük</span>';
    if(['medium','orta'].includes(l))         return '<span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700 border border-amber-200">Orta</span>';
    if(['high','yüksek','yuksek'].includes(l))return '<span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-700 border border-rose-200">Yüksek</span>';
    return '<span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 border border-gray-200">—</span>';
  }
  function typeTr(t){
    const s=String(t||'').toLowerCase();
    if(['corporate','company','kurumsal'].includes(s)) return 'Kurumsal';
    if(['individual','bireysel','personal'].includes(s)) return 'Bireysel';
    return t || '—';
  }

  function openCustomerModal(){ 
    const m=document.getElementById('customerModal'); 
    m.classList.remove('hidden'); 
    postHeight();
  }
  function closeCustomerModal(){ 
    const m=document.getElementById('customerModal'); 
    m.classList.add('hidden'); 
    postHeight();
  }
  function modalLoading(){
    const body=document.getElementById('customerModalBody');
    body.innerHTML = `
      <div class="p-6">
        <div class="animate-pulse space-y-4">
          <div class="h-5 w-40 bg-gray-200 rounded"></div>
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="h-40 bg-gray-200 rounded"></div>
            <div class="h-40 bg-gray-200 rounded"></div>
            <div class="h-40 bg-gray-200 rounded"></div>
            <div class="h-40 bg-gray-200 rounded"></div>
          </div>
        </div>
      </div>`;
  }
  function renderCustomerModal(detail){
    const d = detail?.data || detail || {};
    const model = {
      name:       pick(d,['display_name','name','full_name','company_name','title'],'—'),
      company:    pick(d,['company','company_name','title'],'—'),
      taxNo:      pick(d,['tax_number','tax_no','vkn','vergi_no'],'—'),
      id:         pick(d,['id','customer_id','code'],'—'),
      type:       typeTr(pick(d,['type','customer_type'],'—')),
      status:     (d.status||'pending'),
      email:      pick(d,['email'],'—'),
      phone:      pick(d,['phone'],'—'),
      website:    pick(d,['website','web','url'],'—'),
      contact:    pick(d,['contact_person','authorized','authorized_person','contact_name'],'—'),
      address:    (typeof d.address==='object' ? pick(d.address,['line1','street'],'—') : pick(d,['address'],'—')),
      district:   pick(d,['district','ilce'],'—'),
      city:       pick(d,['city','il','province'],'—'),
      postal:     pick(d,['postal_code','posta_kodu'],'—'),
      credit:     pick(d,['credit_limit','limit'],null),
      balance:    pick(d,['balance','current_balance'],null),
      risk:       pick(d,['risk','risk_level'],''),
      created_at: pick(d,['created_at','createdAt'],''),
      last_tx:    pick(d,['last_transaction_at','last_action_at','updated_at'],''),
      notes:      pick(d,['notes','note','description','remark'],'')
    };

    const body=document.getElementById('customerModalBody');
    body.innerHTML = `
      <div class="p-4 sm:p-6">
        <div class="mb-4 sm:mb-6">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
              <span class="text-blue-600 font-semibold">${esc((model.name||'').slice(0,2).toUpperCase())}</span>
            </div>
            <div>
              <div class="text-lg font-semibold text-gray-900">${esc(model.company||model.name)}</div>
              <div class="text-sm text-gray-500">Müşteri ID: ${esc(model.id)}</div>
            </div>
          </div>
        </div>

       <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 items-stretch">
  <!-- Temel Bilgiler -->
  <div class="rounded-2xl ring-1 ring-gray-100 card-shadow h-full">
    <div class="px-5 py-3 border-b">
      <div class="font-semibold">Temel Bilgiler</div>
    </div>
    <div class="px-5 py-4 grid grid-cols-2 gap-3 text-sm">
      <div class="col-span-2">
        <div class="text-gray-500">Şirket Adı</div>
        <div class="font-medium">${esc(model.company)}</div>
      </div>
      <div>
        <div class="text-gray-500">Vergi Numarası</div>
        <div class="font-medium">${esc(model.taxNo)}</div>
      </div>
      <div>
        <div class="text-gray-500">Müşteri Tipi</div>
        <div>
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700 border border-indigo-200">${esc(model.type)}</span>
        </div>
      </div>
      <div>
        <div class="text-gray-500">Durum</div>
        <div><span class="${statusPillClass(model.status)}">${getStatusText(model.status)}</span></div>
      </div>
    </div>
  </div>

  <!-- İletişim Bilgileri -->
  <div class="rounded-2xl ring-1 ring-gray-100 card-shadow h-full">
    <div class="px-5 py-3 border-b">
      <div class="font-semibold">İletişim Bilgileri</div>
    </div>
    <div class="px-5 py-4 space-y-2 text-sm">
      <div class="flex items-center gap-2"><span class="text-gray-500 w-28">Telefon</span><a class="text-blue-600 hover:underline" href="tel:${esc(model.phone)}">${esc(model.phone)}</a></div>
      <div class="flex items-center gap-2"><span class="text-gray-500 w-28">E-posta</span><a class="text-blue-600 hover:underline" href="mailto:${esc(model.email)}">${esc(model.email)}</a></div>
      <div class="flex items-center gap-2"><span class="text-gray-500 w-28">Yetkili Kişi</span><span class="font-medium">${esc(model.name)}</span></div>
    </div>
  </div>

  <!-- Ek Bilgiler (sol altta) -->
  <div class="rounded-2xl ring-1 ring-gray-100 card-shadow h-full">
    <div class="px-5 py-3 border-b"><div class="font-semibold">Ek Bilgiler</div></div>
    <div class="px-5 py-4 text-sm space-y-2">
      <div class="flex items-center gap-2"><span class="text-gray-500 w-36">Kayıt Tarihi</span><span class="font-medium">${esc(fmtDateTime(model.created_at))}</span></div>
      <div class="flex items-center gap-2"><span class="text-gray-500 w-36">Son İşlem Tarihi</span><span class="font-medium">${esc(fmtDateTime(model.last_tx))}</span></div>
      
    </div>
  </div>

  <!-- Adres Bilgileri (sağ altta) -->
  <div class="rounded-2xl ring-1 ring-gray-100 card-shadow h-full lg:col-start-2">
    <div class="px-5 py-3 border-b"><div class="font-semibold">Adres Bilgileri</div></div>
    <div class="px-5 py-4 text-sm space-y-2">
      <div><span class="text-gray-500 block">Adres</span><div class="font-medium">${esc(model.address)}</div></div>
    </div>
  </div>
</div>`;
  }
 document.addEventListener('keydown', (e)=>{ if(e.key === 'Escape') closeCustomerModal(); });

  </script>

  <!-- ===== Modal: Müşteri Detayları ===== -->
<div id="customerModal" class="fixed inset-0 z-[1000] hidden overflow-y-auto overscroll-contain" role="dialog" aria-modal="true">

  <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeCustomerModal()"></div>

  <div class="relative mx-auto my-6 sm:my-12 w-[92%] max-w-5xl">
<div class="bg-white rounded-2xl shadow-xl ring-1 ring-black/5 overflow-hidden max-h-[90vh] flex flex-col">

     <div class="flex items-center justify-between px-5 py-3 border-b">
  <div class="flex items-center gap-2">
    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z"/>
    </svg>
    <div class="font-semibold">Müşteri Detayları</div>
  </div>

  <div class="flex items-center gap-1">
    <!-- Düzenle -->
    <button class="rounded-lg p-2 hover:bg-gray-100" onclick="editCustomer()" title="Düzenle">
      <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M16.5 3.75a2.121 2.121 0 013 3L7.5 18.75l-4 1 1-4L16.5 3.75z"/>
      </svg>
    </button>

    <!-- Sil -->
    <button class="rounded-lg p-2 hover:bg-rose-50" onclick="deleteCustomer()" title="Sil">
      <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M19 7l-1 12a2 2 0 01-2 2H8a2 2 0 01-2-2L5 7m3-3h8m-7 0l1-1h4l1 1M10 11v6m4-6v6"/>
      </svg>
    </button>

    <!-- Kapat -->
    <button class="rounded-lg p-2 hover:bg-gray-100" onclick="closeCustomerModal()" aria-label="Kapat">
      <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
      </svg>
    </button>
  </div>
</div>
<div id="customerModalBody" class="min-h-[200px] flex-1 overflow-y-auto"></div>

    </div>
  </div>
</div>

</body>
</html>
