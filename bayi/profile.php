<?php
// api/partners_me.php
require_once __DIR__ . '/../config/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

/* ===========================================================
   BU DOSYAYI JSON ENDPOINT GİBİ DE KULLAN
   - Accept: application/json ile gelirse:
     GET    -> /partners/me (profili getir)
     PATCH  -> /partners/me (profili güncelle)
   =========================================================== */
$wants_json = isset($_SERVER['HTTP_ACCEPT']) && stripos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
if ($wants_json) {
  header('Content-Type: application/json; charset=utf-8');

  $tok = $_SESSION['accessToken'] ?? '';
  if (!$tok) {
    http_response_code(401);
    echo json_encode(['error' => 'missing_token']);
    exit;
  }

  // config.php içinde API_BASE tanımlıysa kullan; /auth soneki varsa at
  if (!defined('API_ROOT')) {
    $base = defined('API_BASE') ? rtrim(API_BASE, '/') : '';
    define('API_ROOT', $base ? preg_replace('~/auth/?$~i', '', $base) : '');
  }

  $target = rtrim(API_ROOT, '/') . '/partners/me';
  $method = strtoupper($_SERVER['REQUEST_METHOD']);

  $ch = curl_init($target);
  $headers = [
    'Accept: application/json',
    'Authorization: Bearer ' . $tok,
  ];

  if (in_array($method, ['PATCH','PUT','POST'], true)) {
    $body = file_get_contents('php://input') ?: '{}';
    $headers[] = 'Content-Type: application/json';
    if ($method === 'PATCH') curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
    elseif ($method === 'PUT') curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    else curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
  }

  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => $headers,
    CURLOPT_TIMEOUT        => 20,
  ]);

  $resp   = curl_exec($ch);
  $status = curl_getinfo($ch, CURLINFO_HTTP_CODE) ?: 500;
  curl_close($ch);

  http_response_code($status);
  echo $resp ?: json_encode(['error' => 'empty_response']);
  exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Bayi Profil</title>

  <!-- API bilgileri (HEAD içinde kalmalı) -->
  <meta name="partner-token" content="<?= htmlspecialchars($_SESSION['accessToken'] ?? '', ENT_QUOTES) ?>">
  <meta name="partners-me-url" content="/is-ortaklar-paneli/api/partners_me.php">
  <meta name="partners-update-url" content="/is-ortaklar-paneli/api/partners_update.php">

  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .gradient-bg{background:linear-gradient(135deg,#f3f4f6 0%,#e5e7eb 100%)}
    .card-shadow{box-shadow:0 10px 15px -3px rgba(0,0,0,.1),0 4px 6px -2px rgba(0,0,0,.05)}
    .profile-avatar{background:linear-gradient(135deg,#f59e0b 0%,#d97706 100%)}
    .status-badge{animation:pulse 2s infinite}
    .info-card{transition:all .3s ease}
    .info-card:hover{transform:translateY(-2px);box-shadow:0 20px 25px -5px rgba(0,0,0,.1),0 10px 10px -5px rgba(0,0,0,.04)}
    .pending-glow{box-shadow:0 0 20px rgba(245,158,11,.3)}
    /* Inline edit ekleri */
    .success-message{animation:slideIn .3s ease-out}
    @keyframes slideIn{from{transform:translateY(-10px);opacity:0}to{transform:translateY(0);opacity:1}}
    .edit-mode{background:linear-gradient(135deg,#dbeafe 0%,#bfdbfe 100%);border:2px solid #3b82f6}
    .edit-input{background:#fff;border:1px solid #d1d5db;border-radius:.5rem;padding:.5rem;width:100%;font-size:.875rem}
    .edit-input:focus{outline:none;border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.1)}
    .edit-buttons{display:flex;gap:.5rem;margin-top:.75rem}
    .btn-save{background:#10b981;color:#fff;padding:.5rem 1rem;border-radius:.5rem;font-size:.875rem;font-weight:500}
    .btn-save:hover{background:#059669}
    .btn-cancel{background:#6b7280;color:#fff;padding:.5rem 1rem;border-radius:.5rem;font-size:.875rem;font-weight:500}
    .btn-cancel:hover{background:#4b5563}
  </style>
</head>
<body class="min-h-screen gradient-bg">
<header class="bg-white shadow-sm border-b">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center h-16">
      <div class="flex items-center">
        <div class="w-8 h-8 bg-orange-600 rounded-lg flex items-center justify-center mr-3">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H9m0 0H5m0 0h2M7 7h10M7 11h10M7 15h10"></path>
          </svg>
        </div>
        <h1 class="text-xl font-semibold text-gray-900">Bayi Paneli</h1>
      </div>
      <div class="flex items-center space-x-4">
        <button class="text-gray-500 hover:text-gray-700 transition-colors">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </button>
        <button onclick="logout()" class="text-gray-500 hover:text-gray-700 transition-colors">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
          </svg>
        </button>
      </div>
    </div>
  </div>
</header>

<main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
  <nav class="flex mb-6" aria-label="Breadcrumb">
    <ol class="flex items-center space-x-4">
      <li>
        <div>
          <a href="bayi.php" class="text-gray-400 hover:text-gray-500 transition-colors">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
              <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
            </svg>
          </a>
        </div>
      </li>
      <li>
        <div class="flex items-center">
          <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
          </svg>
          <span class="ml-4 text-sm font-medium text-gray-900">Bayi Profil</span>
        </div>
      </li>
    </ol>
  </nav>
  

  <!-- Kapak -->
  <div class="bg-white rounded-2xl card-shadow mb-6 overflow-hidden">
    <div id="cover-gradient" class="bg-gradient-to-r from-orange-600 to-red-600 px-6 py-8">
      <div class="flex items-center">
       <div id="profile-avatar" class="profile-avatar w-20 h-20 rounded-2xl flex items-center justify-center mr-6">
          <span id="profile-initials" class="text-2xl font-bold text-white">--</span>
        </div>

        <div class="text-white">
          <h1 id="profile-name" class="text-3xl font-bold mb-2">—</h1>
          <p id="profile-type" class="text-orange-100 text-lg mb-2">—</p>
          <div class="flex items-center space-x-4">
            <span id="status-badge" class="status-badge inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-orange-500 text-white pending-glow">—</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bilgi Grid -->
 <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6 items-start">
    <!-- Şirket Bilgileri -->
    <div id="company-card" class="bg-white rounded-2xl card-shadow p-6 info-card">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-gray-900 flex items-center">
          <svg class="w-6 h-6 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H9m0 0H5m0 0h2M7 7h10M7 11h10M7 15h10"></path>
          </svg>
          Şirket Bilgileri
        </h2>
        <button id="edit-company-btn" onclick="editCompany()" class="text-blue-600 hover:text-blue-700 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
          </svg>
        </button>
      </div>

      <!-- DİNamik alan: JS dolduruyor -->
      <div id="company-content" class="space-y-4"></div>
    </div>

    <!-- İletişim Bilgileri -->
    <div id="contact-card" class="bg-white rounded-2xl card-shadow p-6 info-card">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-gray-900 flex items-center">
          <svg class="w-6 h-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 7.89a2 2 0 002.83 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
          </svg>
          İletişim Bilgileri
        </h2>
        <button id="edit-contact-btn" onclick="editContact()" class="text-green-600 hover:text-green-700 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
          </svg>
        </button>
      </div>

      <!-- DİNamik alan: JS dolduruyor -->
      <div id="contact-content" class="space-y-4"></div>
    </div>
  </div>

  <!-- Güvenlik / Durum -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-2xl card-shadow p-6 info-card">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-gray-900 flex items-center">
          <svg class="w-6 h-6 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
          </svg>
          Güvenlik Ayarları
        </h2>
        <button onclick="manageSecurity()" class="text-red-600 hover:text-red-700 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
          </svg>
        </button>
      </div>

      <div class="p-4 bg-red-50 rounded-lg border border-red-200">
        <div class="flex items-center justify-between mb-3">
          <div class="flex items-center">
            <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mr-4">
              <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
              </svg>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-900">İki Faktörlü Doğrulama</p>
              <p id="mfa_text" class="text-red-700 font-medium">Devre Dışı</p>
            </div>
          </div>
          <span id="mfa_badge" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Güvensiz</span>
        </div>

        <div class="grid gap-4 sm:grid-cols-3 mt-4">
          <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700">Yöntem</label>
            <div class="mt-2 flex items-center gap-6">
              <label class="inline-flex items-center gap-2">
                <input type="radio" name="mfa_method" value="sms" class="text-red-600 focus:ring-red-500" checked>
                <span>SMS</span>
              </label>
              <label class="inline-flex items-center gap-2">
                <input type="radio" name="mfa_method" value="email" class="text-red-600 focus:ring-red-500">
                <span>E-Mail</span>
              </label>
            </div>
          </div>

          <div class="sm:col-span-1">
            <label id="mfa_target_label" class="block text-sm font-medium text-gray-700">Telefon</label>
            <input id="mfa_target" type="tel" inputmode="tel" placeholder="+90 5xx xxx xx xx" class="mt-2 w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500 px-3 py-2 bg-white">
          </div>
        </div>

        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg mt-4">
          <div class="flex items-center">
            <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center mr-4">
              <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
              </svg>
            </div>
            <div class="mt-2">
              <p class="text-sm font-medium text-gray-900">Hesap ID</p>
              <p id="account_id" class="text-gray-600 text-xs font-mono">—</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-2xl card-shadow p-6 info-card">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-gray-900 flex items-center">
          <svg class="w-6 h-6 text-orange-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
          </svg>
          Hesap Durumu
        </h2>
      </div>

      <div id="status-card" class="p-4 bg-orange-50 rounded-lg border border-orange-200">
  <div class="flex items-center justify-between mb-3">
    <div class="flex items-center">
      <div>
        <p class="text-sm font-medium text-gray-900">Hesap Durumu</p>
        <p id="status_text" class="text-orange-700 font-medium">—</p>
      </div>
    </div>
    <span id="status-chip"
          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
      Pending
    </span>
  </div>
  <p id="status-desc" class="text-sm text-orange-600">
    Hesabınız yönetici onayı beklemektedir. Onaylandıktan sonra tüm özelliklere erişebileceksiniz.
  </p>
</div>


        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
          <div class="flex items-center">
            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
              </svg>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-900">Kayıt Tarihi</p>
              <p id="created_at" class="text-gray-600">—</p>
            </div>
          </div>
        </div>

        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
          <div class="flex items-center">
            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-4">
              <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
              </svg>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-900">Son Güncelleme</p>
              <p id="updated_at" class="text-gray-600">—</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<script>
(function(){
  const Fallback = "—";
  const safe = v => (v==null || v==="") ? Fallback : v;
  const setText = (id,val)=>{ const el=document.getElementById(id); if(el) el.textContent=safe(val); };
  const initialsFrom = s => !s ? "--" : String(s).trim().split(/\s+/).slice(0,2).map(x=>x[0]?.toUpperCase()||"").join("") || "--";

  function getToken(){
    const raw = (document.querySelector('meta[name="partner-token"]')?.content || '').trim();
    return raw && raw.startsWith('Bearer ') ? raw.slice(7) : raw;
  }
  function getMeUrl(){
    return document.querySelector('meta[name="partners-me-url"]').content;
  }
  function getUpdateUrl(){
  return document.querySelector('meta[name="partners-update-url"]')?.content
         || 'http://34.44.194.247:3001/api/partners/me';
  }
  // UI’daki TR -> API’deki ASCII map’i (sadece gerekirse)
  const legalTypeMap = { 'Şirket':'Sirket', 'Şahıs':'Sahis' };
  const legalTypeDisplay = { 'Sirket': 'Şirket', 'Sahis': 'Şahıs' };

  // 11 haneli TCKN doğrulayıcı
function validateTCKN(v){
  v = String(v||'').replace(/\D/g,'');
  if (!/^[1-9]\d{10}$/.test(v)) return false;
  const d = v.split('').map(Number);
  const odd = d[0]+d[2]+d[4]+d[6]+d[8];
  const even = d[1]+d[3]+d[5]+d[7];
  const d10 = ((odd*7) - even) % 10;
  const d11 = (d.slice(0,10).reduce((a,b)=>a+b,0)) % 10;
  return d[9] === d10 && d[10] === d11;
}

// Sadece değişen alanları çıkar
// Sadece değişen alanları çıkar (API anahtarlarıyla)
function buildPatch(oldData, nv){
  // Görünen -> API karşılığı
  const normalizedLegal = legalTypeMap[nv.legal_type] || nv.legal_type;

  const body = {};

  // company_name, vkn, tax_office, email, phone, address, contact_name
  if ((nv.company_name||'') !== (oldData.company_name||'')) body.company_name = nv.company_name;
  if ((nv.vkn||'')           !== (oldData.vkn||''))           body.vkn          = nv.vkn;
  if ((nv.tax_office||'')    !== (oldData.tax_office||''))    body.tax_office   = nv.tax_office;
  if ((nv.email||'')         !== (oldData.email||''))         body.email        = nv.email;
  if ((nv.phone||'')         !== (oldData.phone||''))         body.phone        = nv.phone;
  if ((nv.address||'')       !== (oldData.address||''))       body.address      = nv.address;
  if ((nv.contact_name||'')  !== (oldData.contact_name||''))  body.contact_name = nv.contact_name;

  // legal_type (Şirket/Şahıs -> Sirket/Sahis)
  if ((normalizedLegal||'') !== (oldData.legal_type||''))     body.legal_type   = normalizedLegal;

  // SADECE ŞAHIS için TCKN gönder
  if ((nv.legal_type === 'Şahıs' || normalizedLegal === 'Sahis')) {
    if ((nv.tckn || '') !== (oldData.tckn || '')) body.tckn = nv.tckn;      // değiştiyse gönder
  }
  // İsterseniz: Şahıs→Şirket’e dönüyorsa TCKN’yi temizlemek için aşağıyı açabilirsiniz:
  // else if (oldData.legal_type === 'Sahis' && oldData.tckn) { body.tckn = null; }

  return body;
}

// Tek bir chip'i statüye göre boya + metnini yaz
function paintChipByStatus(el, s){
  if (!el) return;
  // önce eski stilleri temizle
  el.classList.remove(
    'bg-green-100','text-green-800',
    'bg-yellow-100','text-yellow-800',
    'bg-red-100','text-red-800'
  );

  const st = String(s||'').toLowerCase();
  if (st === 'active') {
    el.classList.add('bg-green-100','text-green-800');
    el.textContent = 'Onaylandı';
  } else if (st === 'pending') {
    el.classList.add('bg-yellow-100','text-yellow-800');
    el.textContent = 'Onay Bekliyor';
  } else if (st === 'inactive' || st === 'blocked') {
    el.classList.add('bg-red-100','text-red-800');
    el.textContent = 'Onaylanmadı';
  } else {
    // bilinmeyen durum (turuncu istersen burada yönetebilirsin)
    el.classList.add('bg-yellow-100','text-yellow-800');
    el.textContent = 'Onay Bekliyor';
  }
}

// Sayfadaki TÜM status-chip'leri boya
function applyGlobalFieldChips(status){
  document.querySelectorAll('.status-chip').forEach(el => paintChipByStatus(el, status));
}


  // ---- Alan rozetleri (e-posta/telefon/adres yanındaki küçük chipler) ----
  // Küçük rozetleri (chip) global statüye göre boya
  function paintChipByStatus(el, s){
    if (!el) return;
    el.classList.remove(
      'bg-green-100','text-green-800',
      'bg-yellow-100','text-yellow-800',
      'bg-red-100','text-red-800',
      'bg-orange-100','text-orange-800'
    );
    const st = String(s||'').toLowerCase();
    if (st === 'active')      { el.classList.add('bg-green-100','text-green-800');   el.textContent = 'Onaylandı'; }
    else if (st === 'pending'){ el.classList.add('bg-yellow-100','text-yellow-800'); el.textContent = 'Onay Bekliyor'; }
    else if (st === 'inactive' || st === 'blocked' || st === 'rejected') {
                              el.classList.add('bg-red-100','text-red-800');       el.textContent = 'Onaylanmadı'; }
    else                      { el.classList.add('bg-orange-100','text-orange-800'); el.textContent = 'Bilinmiyor'; }
  }
  function applyGlobalFieldChips(status){
    document.querySelectorAll('.status-chip').forEach(el => paintChipByStatus(el, status));
  }

  function setStatusUI(status){
    const cover  = document.getElementById('cover-gradient'); // kapak gradyanı
    const badge  = document.getElementById('status-badge');   // header rozeti
    const text   = document.getElementById('status_text');    // kart başlığı
    const card   = document.getElementById('status-card');    // kart kutusu
    const chip   = document.getElementById('status-chip');    // kart içi küçük rozet
    const desc   = document.getElementById('status-desc');    // açıklama metni
    const avatar = document.getElementById('profile-avatar'); // MŞ kutusu

    const rm  = (el, ...cls) => el && el.classList.remove(...cls);
    const add = (el, ...cls) => el && el.classList.add(...cls);

    // Kapak gradyanını sıfırla (bg-gradient-to-r kalsın)
    if (cover){
      rm(cover, 'from-green-600','to-green-800','from-yellow-500','to-yellow-700',
                'from-red-600','to-red-800','from-orange-600','to-red-600');
      add(cover, 'bg-gradient-to-r');
    }

    // Header rozeti reset
    rm(badge,'bg-green-600','bg-yellow-500','bg-red-600','bg-orange-500',
              'ring-2','ring-offset-2','ring-green-300','ring-yellow-300','ring-red-300','animate-pulse');

    // Kart reset
    rm(card, 'bg-green-50','bg-yellow-50','bg-red-50','bg-orange-50',
            'border','border-green-200','border-yellow-200','border-red-200','border-orange-200');
    rm(text, 'text-green-700','text-yellow-700','text-red-700','text-orange-700');
    rm(desc, 'text-green-600','text-yellow-600','text-red-600','text-orange-600');
    rm(chip, 'bg-green-100','text-green-800','bg-yellow-100','text-yellow-800',
            'bg-red-100','text-red-800','bg-orange-100','text-orange-800');

    // Avatar reset (zemin + ring)
    if (avatar){
      avatar.style.backgroundImage = 'none';
      avatar.style.background = 'none';
      rm(avatar,'bg-green-600','bg-yellow-500','bg-red-600','bg-orange-500',
                'ring-4','ring-green-300','ring-yellow-300','ring-red-300','ring-orange-300',
                'ring-offset-2','ring-white','shadow-lg');
    }

    const s = String(status||'').toLowerCase();
    let label = '—';
    let help  = 'Hesabınız yönetici onayı beklemektedir. Onaylandıktan sonra tüm özelliklere erişebileceksiniz.';

    if (s === 'active'){
      label = 'Onaylandı'; help = 'Hesabınız onaylandı. Tüm özellikler aktif.';
      add(badge,'bg-green-600','ring-2','ring-offset-2','ring-green-300');
      add(card,'bg-green-50','border','border-green-200'); add(text,'text-green-700'); add(desc,'text-green-600');
      add(chip,'bg-green-100','text-green-800');
      add(cover,'from-green-600','to-green-800');
      add(avatar,'bg-green-600','ring-4','ring-green-300','ring-offset-2','ring-white','shadow-lg');

    } else if (s === 'pending'){
      label = 'Onay Bekliyor';
      add(badge,'bg-yellow-500','animate-pulse','ring-2','ring-offset-2','ring-yellow-300');
      add(card,'bg-yellow-50','border','border-yellow-200'); add(text,'text-yellow-700'); add(desc,'text-yellow-600');
      add(chip,'bg-yellow-100','text-yellow-800');
      add(cover,'from-yellow-500','to-yellow-700');
      add(avatar,'bg-yellow-500','ring-4','ring-yellow-300','ring-offset-2','ring-white','shadow-lg');

    } else if (s === 'inactive' || s === 'blocked' || s === 'rejected'){
      label = 'Onaylanmadı'; help = 'Hesabınız onaylanmadı. Lütfen destekle iletişime geçin.';
      add(badge,'bg-red-600','ring-2','ring-offset-2','ring-red-300');
      add(card,'bg-red-50','border','border-red-200'); add(text,'text-red-700'); add(desc,'text-red-600');
      add(chip,'bg-red-100','text-red-800');
      add(cover,'from-red-600','to-red-800');
      add(avatar,'bg-red-600','ring-4','ring-red-300','ring-offset-2','ring-white','shadow-lg');

    } else {
      // bilinmeyen -> turuncu
      label = String(status || 'Bilinmiyor');
      add(badge,'bg-orange-500');
      add(card,'bg-orange-50','border','border-orange-200'); add(text,'text-orange-700'); add(desc,'text-orange-600');
      add(chip,'bg-orange-100','text-orange-800');
      add(cover,'from-orange-600','to-red-600');
      add(avatar,'bg-orange-500','ring-4','ring-orange-300','ring-offset-2','ring-white','shadow-lg');
    }

    if (badge) badge.textContent = label;
    if (text)  text.textContent  = label;
    if (chip)  chip.textContent  = label;
    if (desc)  desc.textContent  = help;

    // Tüm alan rozetlerini global statüye göre boya
    applyGlobalFieldChips(s);
  }



  /* ---------------- MFA input oto-doldurma ---------------- */
  let _profileCache = null; // { phone, email }
  function getSelectedMFAMethod(){ return document.querySelector('input[name="mfa_method"]:checked')?.value || 'sms'; }
  function updateMFAInput(force=false){
    const method = getSelectedMFAMethod();
    const inp = document.getElementById('mfa_target');
    const lab = document.getElementById('mfa_target_label');
    if (!inp || !lab) return;
    if (method === 'sms'){
      lab.textContent = 'Telefon';
      inp.type = 'tel'; inp.setAttribute('inputmode','tel'); inp.placeholder = '+90 5xx xxx xx xx';
      if (force || !inp.value) inp.value = _profileCache?.phone || '';
    } else {
      lab.textContent = 'E-Mail';
      inp.type = 'email'; inp.setAttribute('inputmode','email'); inp.placeholder = 'ornek@firma.com';
      if (force || !inp.value) inp.value = _profileCache?.email || '';
    }
  }

  /* ---------------- Inline Edit Durumu ---------------- */
  let profileData = {};              // API'den gelen ham veriyi tutar
  let editModes   = { profile:false };

  // PATCH helper
  async function updateProfile(payload){
  const res = await fetch(getUpdateUrl(), {
    method: 'POST',                     // backend: router.post("/me", ...)
    headers: {
      'Content-Type':'application/json',
      'Accept':'application/json',
      ...(getToken() ? { Authorization: 'Bearer ' + getToken() } : {})
    },
    body: JSON.stringify(payload)
  });
  const data = await res.json().catch(()=> ({}));
  if (!res.ok){
    const msg = data?.detail || data?.message || data?.error || ('Güncelleme başarısız ('+res.status+')');
    throw new Error(msg);
  }
  return data;
  }

  function formatDateTR(value, tz) {
    if (!value) return "—";
    const opts = { year:'numeric', month:'long', day:'2-digit', hour:'2-digit', minute:'2-digit', hour12:false };
    if (tz) opts.timeZone = tz;           // tz vermezsen sistem saatini kullanır
    return new Intl.DateTimeFormat('tr-TR', opts).format(new Date(value));
  }

// Kart içeriklerini statik görünümle yeniden çiz
function renderCompanyContent(){
  const c = document.getElementById('company-content');
  if (!c) return;

  const trLegal = (typeof legalTypeDisplay !== 'undefined')
    ? (legalTypeDisplay[profileData.legal_type] || (profileData.type==='Bayi' ? 'Şirket' : ''))
    : (profileData.legal_type || (profileData.type==='Bayi' ? 'Şirket' : ''));

  c.innerHTML = `
    <!-- Şirket Adı -->
    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
      <div class="flex items-center">
        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H9m0 0H5m0 0h2M7 7h10M7 11h10M7 15h10"></path></svg>
        </div>
        <div>
          <p class="text-sm font-medium text-gray-900">Şirket Adı</p>
          <p id="company_name" class="text-gray-600">${safe(profileData.company_name)}</p>
        </div>
      </div>
      <!-- Statüye göre boyanacak -->
      <span class="status-chip inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">—</span>
    </div>

    <!-- VKN -->
    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
      <div class="flex items-center">
        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
          <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
        </div>
        <div>
          <p class="text-sm font-medium text-gray-900">Vergi Kimlik No</p>
          <p id="vkn" class="text-gray-600">${safe(profileData.vkn)}</p>
        </div>
      </div>
      <span class="status-chip inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">—</span>
    </div>

    <!-- Hukuki Yapı -->
    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
      <div class="flex items-center">
        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-4">
          <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
        </div>
        <div>
          <p class="text-sm font-medium text-gray-900">Hukuki Yapı</p>
          <p id="legal_type" class="text-gray-600">${safe(trLegal)}</p>
        </div>
      </div>
      <span class="status-chip inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">—</span>
    </div>
  `;

  // Yeni chip'leri anında boyamak için:
  applyGlobalFieldChips(profileData.status);
}

function renderContactContent(){
  const c = document.getElementById('contact-content');
  if (!c) return;

  c.innerHTML = `
    <div class="space-y-4">
      <!-- Email -->
      <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
        <div class="flex items-center">
          <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path></svg>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-900">E-posta Adresi</p>
            <p id="email" class="text-gray-600">${safe(profileData.email)}</p>
          </div>
        </div>
        <span class="status-chip inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">—</span>
      </div>

      <!-- Phone -->
      <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
        <div class="flex items-center">
          <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-4">
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-900">Telefon Numarası</p>
            <p id="phone" class="text-gray-600">${safe(profileData.phone)}</p>
          </div>
        </div>
        <span class="status-chip inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">—</span>
      </div>

      <!-- Address -->
      <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
        <div class="flex items-center">
          <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-900">Adres</p>
            <p id="address" class="text-gray-600">${safe(profileData.address)}</p>
          </div>
        </div>
        <span class="status-chip inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">—</span>
      </div>
    </div>
  `;

  // Yeni chip'leri anında boyamak için:
  applyGlobalFieldChips(profileData.status);
}



  // Edit modunu aç
  window.editCompany = function(){ editProfile(); }
  window.editContact = function(){ editProfile(); }

  function editProfile(){
    if (editModes.profile) return;
    editModes.profile = true;

    const companyCard = document.getElementById('company-card');
    const contactCard = document.getElementById('contact-card');
    const companyContent = document.getElementById('company-content');
    const contactContent = document.getElementById('contact-content');
    const companyEditBtn = document.getElementById('edit-company-btn');
    const contactEditBtn = document.getElementById('edit-contact-btn');

    companyCard.classList.add('edit-mode');
    contactCard.classList.add('edit-mode');
    companyEditBtn.style.display = 'none';
    contactEditBtn.style.display = 'none';

    // Şirket formu
    companyContent.innerHTML = `
  <div class="space-y-4">
    <div class="p-4 bg-white rounded-lg border-2 border-blue-200">
      <label class="block text-sm font-medium text-gray-900 mb-2">Şirket Adı</label>
      <input type="text" id="edit-company-name" value="${safe(profileData.company_name)}" class="edit-input">
    </div>

    <div class="p-4 bg-white rounded-lg border-2 border-blue-200">
      <label class="block text-sm font-medium text-gray-900 mb-2">Vergi Kimlik No</label>
      <input type="text" id="edit-vkn" value="${safe(profileData.vkn)}" class="edit-input" maxlength="10" inputmode="numeric">
    </div>

    <div class="p-4 bg-white rounded-lg border-2 border-blue-200">
      <label class="block text-sm font-medium text-gray-900 mb-2">Hukuki Yapı</label>
      <select id="edit-legal-type" class="edit-input">
        <option value="Şirket" ${ (profileData.legal_type==='Sirket' || !profileData.legal_type) ? 'selected':'' }>Şirket</option>
        <option value="Şahıs"  ${  profileData.legal_type==='Sahis' ? 'selected':'' }>Şahıs Şirketi</option>
      </select>
    </div>

    <!-- Sadece Şahıs için TCKN -->
    <div id="tckn-wrapper" class="p-4 bg-white rounded-lg border-2 border-blue-200 ${ (profileData.legal_type==='Sahis') ? '' : 'hidden' }">
      <label class="block text-sm font-medium text-gray-900 mb-2">T.C. Kimlik No</label>
      <input type="text" id="edit-tckn" value="${safe(profileData.tckn)}" class="edit-input" maxlength="11" inputmode="numeric" placeholder="11 haneli TCKN">
      <p class="text-xs text-gray-500 mt-1">Şahıs şirketi için zorunludur.</p>
    </div>
  </div>
`;

    // Şahıs seçilince TCKN bloğunu aç/kapat
    const ltSel = document.getElementById('edit-legal-type');
    ltSel.addEventListener('change', (e)=>{
      document.getElementById('tckn-wrapper')
        .classList.toggle('hidden', e.target.value !== 'Şahıs');
    });

    // İletişim formu
    
 contactContent.innerHTML = `
  <div class="space-y-4">
    <div class="p-4 bg-white rounded-lg border-2 border-green-200">
      <label class="block text-sm font-medium text-gray-900 mb-2">E-posta Adresi</label>
      <input type="email" id="edit-email" value="${safe(profileData.email)}" class="edit-input">
    </div>

    <div class="p-4 bg-white rounded-lg border-2 border-green-200">
      <label class="block text-sm font-medium text-gray-900 mb-2">Telefon Numarası</label>
      <input type="tel" id="edit-phone" value="${safe(profileData.phone)}" class="edit-input" maxlength="11">
    </div>

  <div class="p-4 bg-white rounded-lg border-2 border-green-200">
    <label class="block text-sm font-medium text-gray-900 mb-2">
      Adres
      <span id="addressCounter" class="ml-2 text-xs text-gray-500">0/300</span>
    </label>
    <textarea
      id="edit-address"
      class="edit-input"
      rows="3"
      maxlength="300"
      placeholder="Detaylı adres bilgisi giriniz">${safe(profileData.address)}</textarea>
    <div class="text-xs text-gray-500 mt-1">Maksimum 300 karakter.</div>
  </div>

    <div class="edit-buttons">
      <button onclick="saveProfile()" class="btn-save">
        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        Tüm Bilgileri Kaydet
      </button>
      <button onclick="cancelProfileEdit()" class="btn-cancel">
        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        İptal
      </button>
    </div>
  </div>
`;
  bindAddressCounter();

  }

  window.saveProfile = async function () {
  // 1) Form değerleri
  const newCompanyName = document.getElementById('edit-company-name')?.value.trim() || '';
  const newVkn         = document.getElementById('edit-vkn')?.value.trim() || '';
  const newLegalType   = document.getElementById('edit-legal-type')?.value || '';   // "Şirket" | "Şahıs"
  const newTaxOffice   = document.getElementById('edit-tax-office')?.value.trim() || '';
  const newEmail       = document.getElementById('edit-email')?.value.trim() || '';
  const newPhone       = document.getElementById('edit-phone')?.value.trim() || '';
  const newAddress     = document.getElementById('edit-address')?.value.trim() || '';
  const newContactName = document.getElementById('edit-contact-name')?.value.trim() || '';

  // TCKN (yalnız Şahıs'ta görünür/gönderilir)
  const tcknInputEl    = document.getElementById('edit-tckn');
  const newTckn        = (tcknInputEl && !tcknInputEl.closest('.hidden'))
                        ? (tcknInputEl.value || '').trim()
                        : '';

  // 2) Validasyonlar
  if (!newCompanyName || !newVkn || !newEmail || !newPhone) {
    showMessage('Şirket adı, VKN, e-posta ve telefon zorunlu!', 'error'); return;
  }
  if (newVkn.length !== 10 || !/^\d+$/.test(newVkn)) {
    showMessage('VKN 10 haneli sayı olmalıdır!', 'error'); return;
  }
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(newEmail)) {
    showMessage('Geçerli bir e-posta giriniz!', 'error'); return;
  }
  if (!/^\d{11}$/.test(newPhone.replace(/\s/g,''))) {
    showMessage('Telefon 11 haneli olmalıdır!', 'error'); return;
  }
  if (newAddress.length > 300) {
    showMessage('Adres en fazla 300 karakter olabilir!', 'error'); return;
  }

  // Şahıs ise TCKN zorunlu + geçerli
  const isSahis = (newLegalType === 'Şahıs') || (legalTypeMap[newLegalType] === 'Sahis');
  if (isSahis && !validateTCKN(newTckn)) {
    showMessage('Geçerli bir 11 haneli TCKN giriniz.', 'error'); return;
  }

  // 3) Ekran verileri (karşılaştırma için) – TCKN'yi de ekle
  const newValues = {
    company_name: newCompanyName,
    vkn:          newVkn,
    legal_type:   newLegalType,   // "Şirket" | "Şahıs"  (buildPatch içinde API’ye çevrilecek)
    tax_office:   newTaxOffice,
    email:        newEmail,
    phone:        newPhone,
    address:      newAddress,
    contact_name: newContactName,
    tckn:         newTckn         // sadece Şahıs'ta buildPatch gönderir
  };

  // 4) Sadece değişenleri çıkar (API anahtarlarına buildPatch çevirir)
  const patch = buildPatch(profileData || {}, newValues);
  if (!Object.keys(patch).length) {
    showMessage('Değişiklik yok.', 'error'); return;
  }

  // 5) Gönder
  const btn = document.querySelector('.btn-save');
  const prevDisabled = btn?.disabled;
  if (btn) { btn.disabled = true; btn.classList.add('opacity-50','cursor-not-allowed'); }

  try {
    const updated = await updateProfile(patch);   // POST /api/partners/me
    _profileCache = { phone: updated?.phone || newPhone, email: updated?.email || newEmail };
    showMessage('Tüm profil bilgileri başarıyla güncellendi!', 'success');
    await loadPartnerProfile();                   // UI’yi tazele
    cancelProfileEdit();
  } catch (err) {
    showMessage(err.message || 'Güncelleme hatası', 'error');
  } finally {
    if (btn) { btn.disabled = !!prevDisabled; btn.classList.remove('opacity-50','cursor-not-allowed'); }
  }
};

function bindAddressCounter(){
  const ta = document.getElementById('edit-address');
  const counter = document.getElementById('addressCounter');
  const saveBtn = document.querySelector('.btn-save');
  if (!ta || !counter) return;

  const update = () => {
    const len = ta.value.length;
    counter.textContent = `${len}/300`;
    if (len > 300) {
      saveBtn?.setAttribute('disabled','');
      saveBtn?.classList.add('opacity-50','cursor-not-allowed');
    } else {
      saveBtn?.removeAttribute('disabled');
      saveBtn?.classList.remove('opacity-50','cursor-not-allowed');
    }
  };

  ta.addEventListener('input', update);
  update(); // ilk yüklemede göster
}



  // İptal
  window.cancelProfileEdit = function(){
    editModes.profile = false;
    const companyCard = document.getElementById('company-card');
    const contactCard = document.getElementById('contact-card');
    const companyEditBtn = document.getElementById('edit-company-btn');
    const contactEditBtn = document.getElementById('edit-contact-btn');
    companyCard.classList.remove('edit-mode');
    contactCard.classList.remove('edit-mode');
    companyEditBtn.style.display = 'block';
    contactEditBtn.style.display = 'block';
    renderCompanyContent();
    renderContactContent();
  }

  // Toast
  function showMessage(message, type){
    document.querySelectorAll('.message-toast').forEach(x=>x.remove());
    const messageDiv = document.createElement('div');
    messageDiv.className = `message-toast fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 ${type==='success' ? 'bg-green-500 text-white success-message':'bg-red-500 text-white'}`;
    messageDiv.textContent = message;
    document.body.appendChild(messageDiv);
    setTimeout(()=> messageDiv.remove(), 3000);
  }

  // Güvenlik/destek örnekleri
  window.manageSecurity = function(){ showMessage('Güvenlik ayarları sayfasına yönlendiriliyorsunuz...', 'success'); }
  window.logout = function(){ showMessage('Çıkış yapılıyor...', 'success'); }
  /* ----------------------------------------------------- */

  async function loadPartnerProfile(){
    try{
      const headers = getToken() ? { Authorization: 'Bearer ' + getToken(), Accept:'application/json' } : { Accept:'application/json' };
      const res = await fetch(getMeUrl(), { method:'GET', headers });
      const raw = await res.text();
      if (!res.ok){ showMessage('Profil bilgileri alınamadı ('+res.status+').', 'error'); return; }

      let data;
      try { data = JSON.parse(raw); } catch(e){ console.error('JSON parse hatası', e, raw); showMessage('Geçersiz yanıt alındı.', 'error'); return; }

      // Header
      const displayName = data.company_name || [data.first_name, data.last_name].filter(Boolean).join(' ') || data.email || '';
      setText('profile-name', displayName);
      const ini = document.getElementById('profile-initials'); if (ini) ini.textContent = initialsFrom(displayName);
      setText('profile-type', (data.type || 'Bayi') + (data.legal_type ? ' - ' + data.legal_type : ''));
      setStatusUI(data.status);
      applyGlobalFieldChips(data.status);   // <<< tüm alan rozetlerini senkronize et
      // Tarihler & hesap
      setText('account_id', data.id);
      setText('created_at', formatDateTR(data.created_at, 'UTC'));
      setText('updated_at', formatDateTR(data.updated_at, 'UTC'));


      // MFA
      if ('mfa_enabled' in data || 'mfa_method' in data){
        const t = document.getElementById('mfa_text'), b = document.getElementById('mfa_badge');
        if (t && b){
          if (data.mfa_enabled){
            t.textContent = data.mfa_method ? `${data.mfa_method} ile etkin` : 'Etkin';
            b.textContent = 'Güvende';
            b.classList.remove('bg-red-100','text-red-800');
            b.classList.add('bg-green-100','text-green-800');
          } else {
            t.textContent = 'Devre Dışı';
            b.textContent = 'Güvensiz';
            b.classList.remove('bg-green-100','text-green-800');
            b.classList.add('bg-red-100','text-red-800');
          }
        }
      }

      // MFA hedefi için cache
      _profileCache = { phone: data.phone || '', email: data.email || '' };
      updateMFAInput(true);

      // Sayfa verisi
      profileData = data || {};
      renderCompanyContent();
      renderContactContent();

      console.log('[PROFILE] OK – UI güncellendi');
    }catch(err){
      console.error('[PROFILE] Hata:', err);
      showMessage('Beklenmeyen bir hata oluştu.', 'error');
    }
  }

  // Sayfa yüklenince profili getir ve MFA yöntem değişimlerini dinle
  window.addEventListener('load', ()=>{
    loadPartnerProfile();
    document.querySelectorAll('input[name="mfa_method"]').forEach(r =>
      r.addEventListener('change', ()=>updateMFAInput(true))
    );
  });
})();
</script>
</body>
</html>
