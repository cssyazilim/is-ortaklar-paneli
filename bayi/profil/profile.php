<?php
// bayi/profil/profile.php
require_once __DIR__ . '/../../config/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Bayi Profil</title>

  <!-- API bilgileri (JS bu meta'lardan okur) -->
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
  <!-- breadcrumb -->
  <nav class="flex mb-6" aria-label="Breadcrumb">
    <ol class="flex items-center space-x-4">
      <li>
        <div>
          <a href="/is-ortaklar-paneli/bayi/bayi.php" class="text-gray-400 hover:text-gray-500 transition-colors">
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

  <!-- Grid -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6 items-start">
    <div id="company-card" class="bg-white rounded-2xl card-shadow p-6 info-card">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-gray-900 flex items-center">
          <svg class="w-6 h-6 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H9m0 0H5m0 0h2M7 7h10M7 11h10M7 15h10"></path>
          </svg>
          <span id="company-card-title">Şirket Bilgileri</span>
        </h2>

        <button id="edit-company-btn" onclick="editCompany()" class="text-blue-600 hover:text-blue-700 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
          </svg>
        </button>
      </div>
      <div id="company-content" class="space-y-4"></div>
    </div>

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
      <div id="contact-content" class="space-y-4"></div>
    </div>
  </div>

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
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
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
          <span id="status-chip" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
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
</main>

<!-- Tüm JS dışarıda -->
<script src="/is-ortaklar-paneli/assets/js/profile.js"></script>
</body>
</html>
