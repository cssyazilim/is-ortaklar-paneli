<?php
// Tek seferlik, güvenli oturum başlatma
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// İsteğe bağlı: bayi rolü kontrolünü de buradan yapabilirsin
function require_bayi_role(): void {
    if (empty($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'bayi') {
        header('Location: /is-ortaklar-paneli/login.php');
        exit;
    }
}
