<?php
session_start();
session_unset(); // tüm session değişkenlerini temizle
session_destroy(); // oturumu tamamen yok et

// login sayfasına yönlendir
header("Location: /is-ortaklar-paneli/auth/login.php");
exit;