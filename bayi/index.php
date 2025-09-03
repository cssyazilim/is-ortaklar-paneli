<?php
// /is-ortaklar-paneli/bayi/index.php

/* ---------- Auth ---------- */
session_start();
if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'bayi') {
  header('Location: /is-ortaklar-paneli/login.php'); exit;
}

if (!isset($_GET['page']) && basename($_SERVER['SCRIPT_NAME']) === 'index.php') {
  header('Location: /is-ortaklar-paneli/bayi/dashboard', true, 301);
  exit;
}

require_once __DIR__ . '/../config/config.php';

$userName = $_SESSION['user']['name'] ?? '';
if (!$userName && !empty($_SESSION['user']['email'])) {
    $mail = $_SESSION['user']['email'];
    $parts = explode('@', $mail);
    $local = $parts[0] ?? '';
    $local = str_replace(['.', '_', '-'], ' ', $local); // ayır
    $userName = ucwords($local); // Baş harfleri büyük yap
}
$userName = htmlspecialchars($userName ?: 'Bayi', ENT_QUOTES, 'UTF-8');
/* ---------- Router (slug) ---------- */
/* .htaccess =>  RewriteRule ^bayi/([^/]+)?$ bayi/index.php?page=$1 [QSA,L]  */
$slug = isset($_GET['page']) ? trim($_GET['page'], '/'): '';
if ($slug === '') $slug = 'dashboard';

$ROUTES = [
  'dashboard'     => __DIR__ . '/dashboard.php',
  'musteriKayit'  => __DIR__ . '/musteriKayit.php',
  'musterilerim'  => __DIR__ . '/musterilerim.php',
  'teklifler'     => __DIR__ . '/teklifler.php',
  'teklif-olustur'  => 'teklif-olustur.php',   // <-- BUNU EKLE
  'siparislerim'  => __DIR__ . '/siparislerim.php',
  'hakedis'     => __DIR__ . '/hakedis.php',
];

$content_file = $ROUTES[$slug] ?? null;
$notFound = (!$content_file || !is_file($content_file));

/* ---------- View helpers ---------- */
function active($need){
  global $slug;
  return $slug === $need
    ? 'text-indigo-600 border-b-2 border-indigo-500'
    : 'text-gray-500 hover:text-gray-700';
}

/**
 * İçerik dosyasını göm: alt sayfalar navbar basmasın diye BAYI_EMBED=true gönderiyoruz.
 * Alt sayfa tam HTML basarsa yalnızca <body> içini alırız.
 */
function render_partial(string $file): string {
  if (!defined('BAYI_EMBED')) define('BAYI_EMBED', true);
  ob_start();
  include $file;
  $out = ob_get_clean();
  if (stripos($out, '<body') !== false && preg_match('~<body[^>]*>(.*)</body>~is', $out, $m)) {
    return $m[1];
  }
  return $out;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Bayi Yönetim Sistemi</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/is-ortaklar-paneli/bayi/bayi.css">
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
<!-- NAVBAR -->
<nav class="bg-white shadow-lg border-b border-gray-200">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between h-16">
      
      <!-- Sol Logo -->
      <div class="flex items-center h-full">
        <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-indigo-600">Bayi Yönetim Sistemi</h1>
      </div>

      <!-- Desktop Menü -->
      <div class="hidden md:flex md:space-x-8 md:items-center md:h-full">
        <a href="/is-ortaklar-paneli/bayi/dashboard"    class="nav-btn px-1 h-full flex items-center text-sm font-medium <?= active('dashboard');?>">Dashboard</a>
        <a href="/is-ortaklar-paneli/bayi/musteriKayit" class="nav-btn px-1 h-full flex items-center text-sm font-medium <?= active('musteriKayit');?>">Müşteri Kayıt</a>
        <a href="/is-ortaklar-paneli/bayi/musterilerim" class="nav-btn px-1 h-full flex items-center text-sm font-medium <?= active('musterilerim');?>">Müşterilerim</a>
        <a href="/is-ortaklar-paneli/bayi/teklifler"    class="nav-btn px-1 h-full flex items-center text-sm font-medium <?= active('teklifler');?>">Teklifler</a>
        <a href="/is-ortaklar-paneli/bayi/siparislerim" class="nav-btn px-1 h-full flex items-center text-sm font-medium <?= active('siparislerim');?>">Siparişlerim</a>
        <a href="/is-ortaklar-paneli/bayi/hakedis"    class="nav-btn px-1 h-full flex items-center text-sm font-medium <?= active('hakedis');?>">Hak Ediş</a>
      </div>

   <!-- Kullanıcı Dropdown -->
<div class="relative flex items-center ml-6">
  <button onclick="toggleUserMenu()" 
          class="flex items-center text-sm font-medium text-gray-700 hover:text-gray-900 focus:outline-none">
    Hoş geldiniz, <strong class="ml-1"><?= $userName ?></strong>
    <svg class="ml-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
    </svg>
  </button>

    <!-- Dropdown -->
  <div id="user-menu" 
       class="hidden absolute right-0 top-full mt-1 w-48 bg-white rounded-lg shadow-lg border border-gray-100 z-50 origin-top-right">
    <a href="/is-ortaklar-paneli/bayi/profil/profile.php" 
       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profilim</a>
    <a href="/is-ortaklar-paneli/auth/logout.php" 
       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Çıkış Yap</a>
  </div>
</div>

      <!-- Mobile toggle button -->
      <div class="flex items-center md:hidden">
        <button onclick="toggleMobileMenu()" class="inline-flex items-center justify-center p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none">
          <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
        </button>
      </div>
    </div>
  </div>

  <!-- Mobile Menü -->
<div id="mobile-menu" class="md:hidden hidden px-2 pt-2 pb-3 space-y-1 bg-white border-t border-gray-200">
  <a href="/is-ortaklar-paneli/bayi/dashboard"    class="block px-3 py-2 rounded-md text-sm font-medium <?= active('dashboard');?>">Dashboard</a>
  <a href="/is-ortaklar-paneli/bayi/musteriKayit" class="block px-3 py-2 rounded-md text-sm font-medium <?= active('musteriKayit');?>">Müşteri Kayıt</a>
  <a href="/is-ortaklar-paneli/bayi/musterilerim" class="block px-3 py-2 rounded-md text-sm font-medium <?= active('musterilerim');?>">Müşterilerim</a>
  <a href="/is-ortaklar-paneli/bayi/teklifler"    class="block px-3 py-2 rounded-md text-sm font-medium <?= active('teklifler');?>">Teklifler</a>
  <a href="/is-ortaklar-paneli/bayi/siparislerim" class="block px-3 py-2 rounded-md text-sm font-medium <?= active('siparislerim');?>">Siparişlerim</a>
  <a href="/is-ortaklar-paneli/bayi/hakedis"    class="block px-3 py-2 rounded-md text-sm font-medium <?= active('hakedis');?>">Hak Ediş</a>
  
  <!-- Kullanıcı Menü Mobile -->
  <div class="border-t border-gray-200 pt-2 mt-2">
    <p class="px-3 py-2 text-sm text-gray-600">Hoş geldiniz, <strong><?= $userName ?></strong></p>
    <a href="/is-ortaklar-paneli/bayi/profil/profile.php" 
       class="block px-3 py-2 rounded-md text-sm text-gray-700 hover:bg-gray-100">Profilim</a>
    <a href="/is-ortaklar-paneli/auth/logout.php" 
       class="block px-3 py-2 rounded-md text-sm text-gray-700 hover:bg-gray-100">Çıkış Yap</a>
  </div>
</div>

  
</nav>

  <!-- CONTENT -->
  <main class="max-w-7xl mx-auto py-3 sm:py-6 px-2 sm:px-4 lg:px-8">
    <?php
      if ($notFound) {
        http_response_code(404);
        echo '<div class="bg-white rounded-xl shadow p-8 text-center text-gray-600">Sayfa bulunamadı.</div>';
      } else {
        // Her alt sayfa kendi <html>’ini bastırsa bile sadece <body> içini alıyoruz:
        echo render_partial($content_file);
      }
    ?>
  </main>

  <script src="/is-ortaklar-paneli/bayi/bayi.js"></script>
</body>
</html>
