<?php
// file: /is-ortaklar-paneli/logout.php
if (session_status() === PHP_SESSION_NONE) session_start();

/* ——— Hedefi belirle ——— */
$adminRoles = ['admin','super_admin','merkez'];

$role     = $_SESSION['user']['role']  ?? $_SESSION['role'] ?? null;   // 'admin', 'bayi'...
$scope    = $_SESSION['user']['scope'] ?? null;                        // 'user' (admin tarafı)
$userType = $_SESSION['userType']      ?? ($_SESSION['user']['userType'] ?? null); // 'partner' vs.
$hint     = $_GET['to'] ?? $_GET['t'] ?? null;                         // manuel ipucu: admin | partner | bayi

// Varsayılan: bayi login
$target = '/is-ortaklar-paneli/auth/login.php';

// 1) URL ipucuna göre
if ($hint === 'admin') {
  $target = '/is-ortaklar-paneli/admin/adminlogin.php';
} elseif ($hint === 'partner' || $hint === 'bayi') {
  $target = '/is-ortaklar-paneli/auth/login.php';
} else {
  // 2) Session bilgisine göre
  if ($scope === 'user' || in_array($role, $adminRoles, true)) {
    $target = '/is-ortaklar-paneli/admin/adminlogin.php';
  } elseif ($role === 'bayi' || $userType === 'partner') {
    $target = '/is-ortaklar-paneli/auth/login.php';
  } else {
    // 3) Referer'e göre son çare
    $ref = $_SERVER['HTTP_REFERER'] ?? '';
    if (stripos($ref, '/admin/') !== false) {
      $target = '/is-ortaklar-paneli/admin/adminlogin.php';
    }
  }
}

/* ——— Session’ı güvenli şekilde bitir ——— */
$_SESSION = [];
if (ini_get('session.use_cookies')) {
  $params = session_get_cookie_params();
  setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();

/* ——— Yönlendir ——— */
if (!headers_sent()) {
  header('Location: '.$target, true, 302);
  exit;
}
echo '<script>location.href='.json_encode($target).'</script>';
echo '<noscript><meta http-equiv="refresh" content="0;url='.htmlspecialchars($target, ENT_QUOTES, 'UTF-8').'"></noscript>';
exit;
