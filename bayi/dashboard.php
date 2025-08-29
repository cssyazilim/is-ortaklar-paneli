<?php
// /is-ortaklar-paneli/bayi/dashboard.php

// Embed mi? (iframe içinde)
$EMBED = (isset($_GET['embed']) && $_GET['embed'] === '1');

// Güvenlik & konfig
require_once __DIR__ . '/_boot.php';
require_bayi_role();
require_once __DIR__ . '/../config/config.php';
?>

<?php if (!$EMBED): ?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="/assets/css/bayi.css">
</head>
<body class="bg-gray-50">
<?php endif; ?>

<div class="max-w-7xl mx-auto py-3 sm:py-6 px-2 sm:px-4 lg:px-8">
  <!-- Stats Cards -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6 mb-4 sm:mb-8">
    <div class="bg-white rounded-xl shadow-md p-4 sm:p-6 border border-gray-100">
      <div class="flex items-center">
        <div class="p-2 sm:p-3 rounded-full bg-blue-100">
          <svg class="w-5 h-5 sm:w-6 sm:h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
        </div>
        <div class="ml-3 sm:ml-4">
          <p class="text-xs sm:text-sm font-medium text-gray-600">Aktif Teklifler</p>
          <p class="text-xl sm:text-2xl font-semibold text-gray-900">12</p>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-xl shadow-md p-4 sm:p-6 border border-gray-100">
      <div class="flex items-center">
        <div class="p-2 sm:p-3 rounded-full bg-green-100">
          <svg class="w-5 h-5 sm:w-6 sm:h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
          </svg>
        </div>
        <div class="ml-3 sm:ml-4">
          <p class="text-xs sm:text-sm font-medium text-gray-600">Bu Ay Siparişler</p>
          <p class="text-xl sm:text-2xl font-semibold text-gray-900">8</p>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-xl shadow-md p-4 sm:p-6 border border-gray-100">
      <div class="flex items-center">
        <div class="p-2 sm:p-3 rounded-full bg-yellow-100">
          <svg class="w-5 h-5 sm:w-6 sm:h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
          </svg>
        </div>
        <div class="ml-3 sm:ml-4">
          <p class="text-xs sm:text-sm font-medium text-gray-600">Bekleyen Hakediş</p>
          <p class="text-lg sm:text-2xl font-semibold text-gray-900">₺45,250</p>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-xl shadow-md p-4 sm:p-6 border border-gray-100">
      <div class="flex items-center">
        <div class="p-2 sm:p-3 rounded-full bg-purple-100">
          <svg class="w-5 h-5 sm:w-6 sm:h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
          </svg>
        </div>
        <div class="ml-3 sm:ml-4">
          <p class="text-xs sm:text-sm font-medium text-gray-600">Bu Ay Gelir</p>
          <p class="text-lg sm:text-2xl font-semibold text-gray-900">₺128,500</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent Activities -->
  <div class="bg-white rounded-xl shadow-md p-4 sm:p-6 border border-gray-100">
    <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-4">Son Aktiviteler</h3>
    <div class="space-y-3 sm:space-y-4">
      <div class="flex items-center p-2 sm:p-3 bg-blue-50 rounded-lg">
        <div class="w-2 h-2 bg-blue-500 rounded-full mr-2 sm:mr-3 flex-shrink-0"></div>
        <div class="flex-1 min-w-0">
          <p class="text-xs sm:text-sm font-medium text-gray-900 truncate">Yeni teklif talebi alındı</p>
          <p class="text-xs text-gray-500">ABC Şirketi - 2 saat önce</p>
        </div>
      </div>
      <div class="flex items-center p-2 sm:p-3 bg-green-50 rounded-lg">
        <div class="w-2 h-2 bg-green-500 rounded-full mr-2 sm:mr-3 flex-shrink-0"></div>
        <div class="flex-1 min-w-0">
          <p class="text-xs sm:text-sm font-medium text-gray-900 truncate">Sipariş tamamlandı</p>
          <p class="text-xs text-gray-500">XYZ Ltd - 4 saat önce</p>
        </div>
      </div>
      <div class="flex items-center p-2 sm:p-3 bg-yellow-50 rounded-lg">
        <div class="w-2 h-2 bg-yellow-500 rounded-full mr-2 sm:mr-3 flex-shrink-0"></div>
        <div class="flex-1 min-w-0">
          <p class="text-xs sm:text-sm font-medium text-gray-900 truncate">Ödeme bekliyor</p>
          <p class="text-xs text-gray-500">DEF A.Ş - 1 gün önce</p>
        </div>
      </div>
    </div>
  </div>
</div>

<?php if ($EMBED): ?>
<script>
  // iframe yüksekliği -> parent'a gönder
  function __sendHeight(){
    var h = Math.max(
      document.body.scrollHeight,
      document.documentElement.scrollHeight
    );
    parent.postMessage({type:'resize-iframe', height:h}, '*');
  }
  window.addEventListener('load', __sendHeight);
  new ResizeObserver(__sendHeight).observe(document.body);
</script>
<?php endif; ?>

<?php if (!$EMBED): ?>
</body>
</html>
<?php endif; ?>
