<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

/** UYGULAMA URL KÖKÜ (web root'un public/ ise aşağıdaki iyi) */
define('BASE', '/is-ortaklar-paneli/public/');

/** DIŞ API AYARLARI */
define('API_BASE', 'http://34.44.194.247:3000'); // gerekirse https yap

/** Rol -> Panel yönlendirme eşlemesi */
const ROLE_TO_ROUTE = [
  'admin'         => 'admin/anasayfa.php',
  'partner_user'  => 'bayi/bayi.php',
  // gerekirse 'dealer', 'super_admin' vb. ekle
];

/** Küçük yardımcılar */
function e($str){ return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8'); }

function redirect_if_logged_in() {
  if (!empty($_SESSION['user']['role'])) {
    $route = ROLE_TO_ROUTE[$_SESSION['user']['role']] ?? 'bayi/bayi.php';
    header('Location: ' . BASE . $route);
    exit;
  }
}

/** Oturum zorunlu sayfa koruması */
function require_role(array $allowed){
  $role = $_SESSION['user']['role'] ?? null;
  if (!$role || !in_array($role, $allowed, true)) {
    header('Location: '.BASE.'index.php');
    exit;
  }
}
