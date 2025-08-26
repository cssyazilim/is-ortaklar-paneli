<?php
// file: auth/session_check.php
require_once __DIR__ . '/../config/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: text/plain; charset=utf-8');

// Token’ı güvenlik için maskeli gösterelim
function mask($s){ 
    $s=(string)$s; 
    return $s ? substr($s,0,12).'...'.substr($s,-8) : '(boş)'; 
}

echo "=== SESSION CHECK ===\n\n";
echo "Session name : ".session_name()."\n";
echo "Session id   : ".session_id()."\n\n";

$acc   = $_SESSION['access_token']          ?? '';
$accU  = $_SESSION['user']['access_token']  ?? '';
$role  = $_SESSION['user']['role']          ?? '(yok)';
$pid   = $_SESSION['user']['partner_id']    ?? '(yok)';

echo "[access_token]       ".mask($acc)."   len=".strlen($acc)."\n";
echo "[user.access_token]  ".mask($accU)."   len=".strlen($accU)."\n";
echo "[user.role]          ".$role."\n";
echo "[user.partner_id]    ".$pid."\n";

echo "\nTüm SESSION içeriği:\n";
print_r($_SESSION);
