<?php
// file: /is-ortaklar-paneli/bayi/musteriKayit.php

// --- AJAX: aynı sayfada create endpoint (proxy) ---
// 1) Tarayıcı POST'u bu sayfaya yapacak: ?ajax=create_customer
// 2) PHP cURL ile Node'a iletecek: http://34.44.194.247:3001/api/customers
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_GET['ajax']) && $_GET['ajax'] === 'create_customer') {
    header('Content-Type: application/json; charset=utf-8');

    // Sadece POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'method_not_allowed']);
        exit;
    }

    // Bayi oturumu kontrolü
    if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'bayi') {
        http_response_code(403);
        echo json_encode(['error' => 'forbidden']);
        exit;
    }

    // Node API adresi
    $node = 'http://34.44.194.247:3001/api/customers';

    // Authorization: Önce gelen header, yoksa session'dan.
    $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!$auth) {
        $tok = $_SESSION['partnerAccessToken'] ?? $_SESSION['bayiAccessToken'] ?? $_SESSION['accessToken'] ?? '';
        if ($tok && stripos($tok, 'Bearer ') !== 0) $tok = 'Bearer '.$tok;
        if ($tok) $auth = $tok;
    }

    $body = file_get_contents('php://input') ?: '{}';

    // cURL ile Node'a POST
    $ch = curl_init($node);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => 'POST',
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => false,
        CURLOPT_HTTPHEADER     => array_values(array_filter([
            'Accept: application/json',
            'Content-Type: application/json',
            $auth ? 'Authorization: '.$auth : null,
        ])),
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT        => 30,
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE) ?: 0;
    $err  = curl_error($ch);
    curl_close($ch);

    if ($resp === false) {
        http_response_code(502);
        echo json_encode(['error'=>'proxy_error','detail'=>$err ?: 'upstream_unreachable']);
        exit;
    }

    http_response_code($code);
    echo $resp;
    exit;
}

// --- normal sayfa akışı (render) ---
if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'bayi') {
  header('Location: /is-ortaklar-paneli/login.php');
  exit;
}

/* Embed kontrolü (iframe için) */
$EMBED = (
  isset($_GET['embed']) ||
  isset($_SERVER['HTTP_X_EMBED']) ||
  (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])==='xmlhttprequest')
);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Müşteri Kayıt</title>
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Backend için meta (token/endpoint) -->
  <meta name="partner-token" content="<?= htmlspecialchars($_SESSION['partnerAccessToken'] ?? $_SESSION['bayiAccessToken'] ?? $_SESSION['accessToken'] ?? '', ENT_QUOTES) ?>">
  <meta name="partners-me-url" content="/is-ortaklar-paneli/api/partners_me.php">
  <!-- Aşağıdaki meta kalsın; JS zaten aynı sayfayı kullanacak -->
  <meta name="customers-url" content="http://34.44.194.247:3001/api/customers">

  <?php if(!$EMBED): ?>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/bayi.css">
  <?php endif; ?>
  <style>
/* Seçili tip kartını vurgula (kalıcı mavi çerçeve) */
.type-card.active{
  border-color:#3b82f6;
  background:#eef2ff;
  box-shadow:0 0 0 2px rgba(59,130,246,.35),
             0 0 0 2px rgba(79,70,229,.25) inset;
}
.type-card.active .group-[.active]\:text-indigo-600{ color:#4f46e5 !important; }
.type-card.active .group-[.active]\:text-indigo-700{ color:#4338ca !important; }

.card-shadow{ box-shadow:0 4px 6px -1px rgba(0,0,0,.08), 0 2px 4px -1px rgba(0,0,0,.04); }
#workflow-stepper { min-height: 96px; }
  </style>
</head>
<body class="<?php echo $EMBED ? 'bg-transparent' : 'bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen'; ?>">

  <div class="<?php echo $EMBED ? '' : 'max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8'; ?>">
    <!-- Tek beyaz kart -->
    <section class="rounded-2xl bg-white ring-1 ring-gray-100 card-shadow overflow-hidden">
      <div class="p-4 sm:p-6 lg:p-8">
        <div class="mb-4 sm:mb-8">
          <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Müşteri Kayıt ve Onay Sürecim</h2>
          <p class="text-sm sm:text-base text-gray-600">Yeni müşteri kayıt işlemleri ve onay durumum</p>
        </div>

        <!-- Süreç adımları (dinamik) -->
        <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6 mb-4 sm:mb-6">
          <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-4">Kayıt Süreci Adımlarım</h3>
          <div id="workflow-stepper" class="overflow-x-auto pb-2"></div>
        </div>

        <!-- Kayıt formu -->
<div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
  <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-4">Yeni Müşteri Kayıt Formu</h3>

  <form id="kayitForm" class="space-y-5">

    <!-- Müşteri Tipi -->
    <section class="rounded-xl border border-gray-200 p-4 sm:p-5">
      <div class="flex items-center gap-2 mb-3">
        <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
        </svg>
        <h4 class="text-sm sm:text-base font-semibold text-gray-900">Müşteri Tipi</h4>
      </div>

      <input type="hidden" name="type" id="f_type" value="Sahis"/>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <!-- Şirket kartı -->
        <button type="button" id="btnSirket"
          class="type-card group flex items-center gap-4 rounded-xl border p-4 transition hover:border-indigo-300 focus:outline-none"
          data-type="Sirket" aria-pressed="false">
          <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center">
            <svg class="w-6 h-6 text-gray-600 group-[.active]:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M5 21V7a2 2 0 012-2h5v5h5v11M14 5l5 5"/>
            </svg>
          </div>
          <div class="text-left">
            <div class="font-semibold text-gray-900 group-[.active]:text-indigo-700">Şirket</div>
            <div class="text-xs text-gray-500">Kurumsal müşteri</div>
          </div>
        </button>

        <!-- Şahıs kartı -->
        <button type="button" id="btnSahis"
          class="type-card group flex items-center gap-4 rounded-xl border p-4 transition hover:border-indigo-300 focus:outline-none active"
          data-type="Sahis" aria-pressed="true">
          <div class="w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center">
            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
          </div>
          <div class="text-left">
            <div class="font-semibold text-gray-900">Şahıs</div>
            <div class="text-xs text-gray-500">Bireysel müşteri</div>
          </div>
        </button>
      </div>
    </section>

    <!-- Şahıs Bilgileri -->
    <section id="sahisBox" class="rounded-xl border border-gray-200 p-4 sm:p-5">
      <div class="flex items-center gap-2 mb-3">
        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
        <h4 class="text-sm sm:text-base font-semibold text-gray-900">Şahıs Bilgileri</h4>
      </div>

      <div class="grid grid-cols-1 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Ad Soyad <span class="text-red-500">*</span></label>
          <input name="name" id="f_name" type="text" placeholder="Örn: Ahmet Yılmaz"
                 class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">T.C. Kimlik Numarası <span class="text-red-500">*</span></label>
          <input name="tckn" id="f_tckn" inputmode="numeric" maxlength="11" placeholder="11 haneli TCKN"
                 class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        </div>
      </div>
    </section>

    <!-- Şirket Bilgileri -->
    <section id="sirketBox" class="hidden rounded-xl border border-gray-200 p-4 sm:p-5">
      <div class="flex items-center gap-2 mb-3">
        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M5 21V7a2 2 0 012-2h5v5h5v11M14 5l5 5"/>
        </svg>
        <h4 class="text-sm sm:text-base font-semibold text-gray-900">Şirket Bilgileri</h4>
      </div>

      <div class="grid grid-cols-1 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Şirket Unvanı <span class="text-red-500">*</span></label>
          <input name="title" id="f_title" type="text" placeholder="ABC Teknoloji A.Ş."
                 class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Vergi Kimlik Numarası (VKN) <span class="text-red-500">*</span></label>
          <input name="vkn" id="f_vkn" inputmode="numeric" maxlength="10" placeholder="10 haneli VKN"
                 class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        </div>
      </div>
    </section>

    <!-- İletişim Bilgileri -->
    <section class="rounded-xl border border-gray-200 p-4 sm:p-5">
      <div class="flex items-center gap-2 mb-3">
        <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
        </svg>
        <h4 class="text-sm sm:text-base font-semibold text-gray-900">İletişim Bilgileri</h4>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
  <div>
    <label class="block text-sm font-medium text-gray-700 mb-1">E-posta Adresi</label>
    <input name="email" id="f_email" type="email" placeholder="ornek@email.com"
           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
  </div>
  <div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Telefon Numarası</label>
    <input name="phone" id="f_phone" inputmode="numeric" maxlength="11" placeholder="05xxxxxxxxx"
           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
  </div>
</div>

<!-- Yalnızca şu satırı mt-4 ile güncelledik -->
<div class="mt-4">
  <label class="block text-sm font-medium text-gray-700 mb-1">Çalışan Sayısı</label>
  <input name="employee" id="f_employee" inputmode="numeric" pattern="[0-9]*" maxlength="6" placeholder="50"
         class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
</div>

<div class="mt-4">
  <label class="block text-sm font-medium text-gray-700 mb-1">Adres</label>
  <textarea name="address" id="f_address" rows="3" placeholder="Tam adres bilgisi..."
            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
</div>

    </section>

    
    <!-- Durum -->
    <section class="rounded-xl border border-gray-200 p-4 sm:p-5">
      <div class="flex items-center gap-2 mb-4">
        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 22a10 10 0 100-20 10 10 0 000 20z" />
        </svg>
        <h4 class="text-sm sm:text-base font-semibold text-gray-900">Durum</h4>
      </div>

      <div>
        <label for="f_status" class="block text-sm font-medium text-gray-700 mb-1">Müşteri Durumu</label>
        <select id="f_status" name="status"
                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500">
          <option value="active" selected>Aktif</option>
          <option value="pending">Beklemede</option>
          <option value="inactive">Pasif</option>
        </select>
      </div>
    </section>

    <div class="flex justify-end pt-1">
      <button id="submitBtn" type="submit"
              class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 sm:px-6 py-2.5 rounded-lg text-sm font-medium transition-colors">
        Kayıt Başvurusu Gönder
      </button>
    </div>
  </form>

  <p id="formMsg" class="mt-3 text-sm hidden">Başvurunuz alındı.</p>
</div>

      </div>
    </section>
  </div>

<script>
// ====== Tip seçimi (mavi çerçeve kalıcı) ======
(function(){
  const btnSirket = document.getElementById('btnSirket');
  const btnSahis  = document.getElementById('btnSahis');
  const fType     = document.getElementById('f_type');

  const sahisBox  = document.getElementById('sahisBox');
  const sirketBox = document.getElementById('sirketBox');

  const fName  = document.getElementById('f_name');
  const fTCKN  = document.getElementById('f_tckn');
  const fTitle = document.getElementById('f_title');
  const fVKN   = document.getElementById('f_vkn');

  function setType(tp){
    fType.value = tp;
    const isSahis = tp === 'Sahis';

    const markActive = (el, on) => {
      el.classList.toggle('active', on);
      el.classList.toggle('ring-2', on);
      el.classList.toggle('ring-indigo-500', on);
      el.classList.toggle('border-indigo-500', on);
      el.classList.toggle('bg-indigo-50', on);
    };

    // Kart vurguları
    markActive(btnSahis, isSahis);
    markActive(btnSirket, !isSahis);
    btnSahis.setAttribute('aria-pressed', String(isSahis));
    btnSirket.setAttribute('aria-pressed', String(!isSahis));

    // Bölümleri göster/gizle
    sahisBox.classList.toggle('hidden', !isSahis);
    sirketBox.classList.toggle('hidden', isSahis);

    // Zorunluluklar
    fName.required  = isSahis;
    fTCKN.required  = isSahis;
    fTitle.required = !isSahis;
    fVKN.required   = !isSahis;
  }

  window.setType = setType;

  btnSahis?.addEventListener('click', ()=> setType('Sahis'));
  btnSirket?.addEventListener('click', ()=> setType('Sirket'));

  function digitsOnly(el, max){ if(!el) return; el.addEventListener('input', ()=>{ el.value = el.value.replace(/\D/g,'').slice(0,max); }); }
  digitsOnly(fTCKN, 11);
  digitsOnly(fVKN, 10);
  digitsOnly(document.getElementById('f_phone'), 11);
  digitsOnly(document.getElementById('f_employee'), 6);

  setType('Sahis');
})();
</script>

<script>
// ====== Stepper (değiştirmedim) ======
function showStepperLoading() {
  const col = (w) => `<div class="h-4 bg-gray-200 rounded ${w} mx-auto"></div>`;
  const circles = Array.from({length:5}).map(() => `<div class="w-10 h-10 bg-gray-200 rounded-full"></div>`).join(`<div class="flex-1 h-1 mx-3 sm:mx-6 bg-gray-200 rounded-full"></div>`);
  document.getElementById('workflow-stepper').innerHTML = `
    <div class="animate-pulse">
      <div class="flex items-center h-14">${circles}</div>
      <div class="grid grid-cols-5 gap-x-3 sm:gap-x-6 mt-2">
        ${col('w-20')} ${col('w-24')} ${col('w-28')} ${col('w-16')} ${col('w-16')}
      </div>
    </div>`;
}
function resolveStage(payload) {
  const status = String(payload?.status || '').toLowerCase();
  if (status === 'active')   return { stage: 5, errorAt: null };
  if (status === 'pending')  return { stage: 2, errorAt: null };
  if (status === 'inactive') return { stage: 4, errorAt: 4 };
  return { stage: 2, errorAt: null };
}
function renderStepper(stage = 2, errorAt = null) {
  const steps = [
    { n: 1, label: 'Form Doldurma' },
    { n: 2, label: 'Operasyon İnceleme' },
    { n: 3, label: 'Ek Evrak Bekleniyor' },
    { n: 4, label: 'Onay' },
    { n: 5, label: 'Giriş Aktif' },
  ];
  const stateOf = (i) => { if (i === errorAt) return 'error'; if (i < stage) return 'done'; if (i === stage) return (stage === 5 ? 'done' : 'current'); return 'todo'; };
  const circleCls = (st) => st==='error'?'bg-red-500 text-white':st==='done'?'bg-green-500 text-white':st==='current'?'bg-amber-400 text-white':'bg-gray-200 text-gray-600';
  const segCls = (st) => st==='error'?'bg-red-500':st==='done'?'bg-green-500':st==='current'?'bg-amber-400':'bg-gray-300';
  let top = `<div class="flex items-center h-14">`;
  steps.forEach((s, idx) => {
    const st = stateOf(s.n);
    top += `<div class="flex items-center"><div class="w-10 h-10 ${circleCls(st)} rounded-full flex items-center justify-center font-bold">${s.n}</div></div>`;
    if (idx < steps.length - 1) { const nextState = stateOf(s.n + 1); top += `<div class="flex-1 h-1 mx-3 sm:mx-6 ${segCls(nextState)} rounded-full"></div>`; }
  });
  top += `</div>`;
  let labels = `<div class="grid grid-cols-5 gap-x-3 sm:gap-x-6 mt-2">`;
  steps.forEach(s => { labels += `<div class="text-center text-xs sm:text-sm text-gray-700 leading-4">${s.label}</div>`; });
  labels += `</div>`;
  document.getElementById('workflow-stepper').innerHTML = top + labels;
}
async function loadWorkflow() {
  showStepperLoading();
  try {
    const tokenMeta = document.querySelector('meta[name="partner-token"]');
    const urlMeta   = document.querySelector('meta[name="partners-me-url"]');
    const token = (tokenMeta?.content || '').trim();
    const url   = (urlMeta?.content   || '/is-ortaklar-paneli/api/partners_me.php').trim();
    const res = await fetch(url, { headers: { 'Accept': 'application/json', ...(token ? { 'Authorization': token.startsWith('Bearer ') ? token : 'Bearer ' + token } : {}) } });
    const data = await res.json();
    const payload = data?.partner || data;
    const { stage, errorAt } = resolveStage(payload);
    renderStepper(stage, errorAt);
  } catch { renderStepper(2, null); }
}
loadWorkflow();
</script>

<script>
// ====== Kayıt gönderme (aynı sayfa AJAX) ======

function validateTCKN(v){
  v = String(v||'').replace(/\D/g,'');
  if (!/^[1-9]\d{10}$/.test(v)) return false;
  const d = v.split('').map(Number);
  const odd  = d[0]+d[2]+d[4]+d[6]+d[8];
  const even = d[1]+d[3]+d[5]+d[7];
  const d10  = ((odd*7) - even) % 10;
  const d11  = (d.slice(0,10).reduce((a,b)=>a+b,0)) % 10;
  return d[9] === d10 && d[10] === d11;
}

function showFormMsg(text, ok=true){
  const el = document.getElementById('formMsg');
  if (!el) return;
  el.classList.remove('hidden','text-green-600','text-red-600');
  el.textContent = text;
  el.classList.add(ok ? 'text-green-600' : 'text-red-600');
}

function getToken(){
  const raw = (document.querySelector('meta[name="partner-token"]')?.content || '').trim();
  return raw ? (raw.startsWith('Bearer ') ? raw : 'Bearer ' + raw) : '';
}

// >>> BURASI ÖNEMLİ: Artık aynı sayfaya post ediyoruz, CORS yok
function getCustomersUrl(){
  const here = window.location.pathname + (window.location.search ? window.location.search + '&' : '?') + 'ajax=create_customer';
  return here;
}

document.getElementById('kayitForm')?.addEventListener('submit', async (e)=>{
  e.preventDefault();
  showFormMsg('', true);

  const type    = document.getElementById('f_type').value;
  const status  = document.getElementById('f_status').value || 'active';
  const email   = (document.getElementById('f_email').value || '').trim();
  const phone   = (document.getElementById('f_phone').value || '').replace(/\D/g,'');
  const address = (document.getElementById('f_address').value || '').trim();

  const employeeRaw = (document.getElementById('f_employee').value || '').replace(/\D/g,'');
  const employeeNum = employeeRaw ? parseInt(employeeRaw, 10) : 0;

  let payload = { type, status };
  let errors  = [];

  if (type === 'Sahis'){
    const name = (document.getElementById('f_name').value || '').trim();
    const tckn = (document.getElementById('f_tckn').value || '').replace(/\D/g,'');
    if (!name) errors.push('Ad Soyad zorunludur.');
    if (!validateTCKN(tckn)) errors.push('Geçerli bir T.C. Kimlik Numarası giriniz.');
    payload.name = name;
    payload.tckn = tckn;
  } else {
    const title = (document.getElementById('f_title').value || '').trim();
    const vkn   = (document.getElementById('f_vkn').value || '').replace(/\D/g,'');
    if (!title) errors.push('Şirket unvanı zorunludur.');
    if (!/^\d{10}$/.test(vkn)) errors.push('VKN 10 haneli olmalıdır.');
    payload.title = title;
    payload.vkn   = vkn;
  }

  if (email){
    const ok = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    if (!ok) errors.push('Geçerli bir e-posta adresi giriniz.');
    else payload.email = email;
  }
  if (phone){
    if (!/^\d{11}$/.test(phone)) errors.push('Telefon 11 haneli olmalıdır.');
    else payload.phone = phone;
  }
  if (address) payload.address = address;

  payload.user_count = Number.isFinite(employeeNum) ? employeeNum : 0;

  if (errors.length){
    showFormMsg(errors[0], false);
    return;
  }

  const btn = document.getElementById('submitBtn');
  const prev = { disabled: btn.disabled, class: btn.className, text: btn.textContent };
  btn.disabled = true;
  btn.textContent = 'Gönderiliyor...';
  btn.classList.add('opacity-70','cursor-not-allowed');

  try{
    const res = await fetch(getCustomersUrl(), {
      method:'POST',
      headers:{
        'Content-Type':'application/json',
        'Accept':'application/json',
        ...(getToken() ? { 'Authorization': getToken() } : {})
      },
      body: JSON.stringify(payload)
    });

    const text = await res.text();
    let data = null; try{ data = text ? JSON.parse(text) : null; }catch{}

    if (!res.ok){
      const detail = data?.detail || data?.message || data?.error || `Hata (${res.status})`;
      showFormMsg(detail, false);
      return;
    }

    showFormMsg('Başvurunuz alındı. Müşteri kaydı oluşturuldu.', true);
    document.getElementById('kayitForm').reset();
    document.getElementById('f_type').value = 'Sahis';
    if (typeof setType === 'function') setType('Sahis');
  } catch(err){
    showFormMsg('Beklenmeyen bir hata oluştu.', false);
    console.error('[POST customers] error:', err);
  } finally {
    btn.disabled = prev.disabled;
    btn.textContent = prev.text;
    btn.className = prev.class;
  }
});
</script>

</body>
</html>
