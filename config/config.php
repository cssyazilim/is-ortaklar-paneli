<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

/** URL kökü */
define('BASE', '/is-ortaklar-paneli/');

/** DIŞ API AYARLARI — SONUNA /api/auth/ KOY! */
define('API_BASE', 'http://34.44.194.247:3001/api/auth/');

/** Rol -> route */
const ROLE_TO_ROUTE = [
  'admin'        => 'admin/anasayfa.php',
  'partner'      => 'bayi/bayi.php',
  'bayi'         => 'bayi/bayi.php',
  'partner_user' => 'bayi/bayi.php',
];


function e($str){ return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8'); }

if (!function_exists('url')) {
  function url(string $p){ return BASE . ltrim($p, '/'); }
}
if (!function_exists('asset_url')) {
  function asset_url(string $p){ return BASE . 'assets/' . ltrim($p,'/'); }
}


function redirect_if_logged_in() {
  if (!empty($_SESSION['user']['role'])) {
    $role = $_SESSION['user']['role'];
    $route = ROLE_TO_ROUTE[$role] ?? 'bayi/bayi.php';
    header('Location: ' . url($route)); exit;
  }
}

function require_role(array $allowed){
  $role = $_SESSION['user']['role'] ?? null;
  if (!$role || !in_array($role, $allowed, true)) {
    header('Location: '.url('index.php')); exit;
  }
}
