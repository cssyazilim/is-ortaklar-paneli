<?php
// auth/login.php
require_once __DIR__ . '/../config/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

/* ---------- Debug (isteğe bağlı log) ---------- */
ini_set('display_errors', 0);
error_reporting(E_ALL);
function _dbg($m){ @file_put_contents(__DIR__.'/login-debug.log','['.date('c')."] $m\n",FILE_APPEND); }

/* ---------- Backend base ---------- */
if (!defined('API_BASE')) {
  define('API_BASE','http://34.44.194.247:3001/api/auth');
}

if (!function_exists('url')) {
  function url(string $p){ return $p; }
}

/* ---------- Güvenli redirect ---------- */
function safe_redirect(string $url,int $code=302):void{
  if(!headers_sent()){
    header('Location: '.$url,true,$code);
    session_write_close(); exit;
  }
  echo '<script>location.href='.json_encode($url).';</script>';
  echo '<noscript><meta http-equiv="refresh" content="0;url='.htmlspecialchars($url,ENT_QUOTES,'UTF-8').'"></noscript>';
  session_write_close(); exit;
}

/* ---------- HTTP yardımcıları ---------- */
function api_post_json(string $path,array $body):array{
  $url=rtrim(API_BASE,'/').'/'.ltrim($path,'/');
  $payload=json_encode($body,JSON_UNESCAPED_UNICODE);
  _dbg("POST $url :: $payload");

  $ch=curl_init($url);
  curl_setopt_array($ch,[
    CURLOPT_RETURNTRANSFER=>true,
    CURLOPT_POST=>true,
    CURLOPT_HTTPHEADER=>['Content-Type: application/json','Accept: application/json'],
    CURLOPT_POSTFIELDS=>$payload,
    CURLOPT_TIMEOUT=>20,
  ]);
  $raw=curl_exec($ch);
  $code=curl_getinfo($ch,CURLINFO_HTTP_CODE);
  $err=curl_error($ch);
  curl_close($ch);

  _dbg("RESP($code): ".substr((string)$raw,0,400));

  if($raw===false) throw new RuntimeException('Sunucuya ulaşılamadı: '.$err);
  $json=json_decode($raw,true);
  if(!is_array($json)){
    if(stripos($raw,'Cannot ')!==false || stripos($raw,'<!DOCTYPE')!==false){
      throw new RuntimeException('Yanlış endpoint: '.$url.' → '.strip_tags($raw));
    }
    throw new RuntimeException('Geçersiz yanıt: '.$raw);
  }
  if($code<200||$code>=300){
    $msg=$json['message']??$json['error']??('HTTP '.$code);
    throw new RuntimeException($msg);
  }
  return $json;
}
function api_get_json(string $path,array $headers=[]):array{
  $url=rtrim(API_BASE,'/').'/'.ltrim($path,'/');
  $h=array_merge(['Accept: application/json'],$headers);
  _dbg("GET $url");

  $ch=curl_init($url);
  curl_setopt_array($ch,[
    CURLOPT_RETURNTRANSFER=>true,
    CURLOPT_HTTPHEADER=>$h,
    CURLOPT_TIMEOUT=>20,
  ]);
  $raw=curl_exec($ch);
  $code=curl_getinfo($ch,CURLINFO_HTTP_CODE);
  $err=curl_error($ch);
  curl_close($ch);

  _dbg("RESP($code): ".substr((string)$raw,0,400));

  if($raw===false) throw new RuntimeException('Sunucuya ulaşılamadı: '.$err);
  $json=json_decode($raw,true);
  if(!is_array($json)) throw new RuntimeException('Geçersiz yanıt: '.$raw);
  if($code<200||$code>=300){
    $msg=$json['message']??$json['error']??('HTTP '.$code);
    throw new RuntimeException($msg);
  }
  return $json;
}

/* ---------- MFA sinyali ---------- */
function resp_requires_mfa(array $r):bool{
  if(($r['status']??null)==='MFA_REQUIRED') return true;
  if(isset($r['mfa']['session_id'])) return true;
  return false;
}

/* ---------- Sarmalayıcılar (yalnızca BAYİ) ---------- */
function api_login_partner(string $e,string $p):array{ return api_post_json('login/partner',['email'=>$e,'password'=>$p]); }
function api_me(string $at):array{ return api_get_json('me',['Authorization: Bearer '.$at]); }

/* ---------- Session yaz ---------- */
function save_tokens_and_user(array $resp,array $who):void{
  $_SESSION['accessToken']      = $resp['accessToken']        ?? null;
  $_SESSION['refreshToken']     = $resp['refreshToken']       ?? null;
  $_SESSION['sessionId']        = $resp['sessionId']          ?? null;
  $_SESSION['refreshExpiresAt'] = $resp['refresh_expires_at'] ?? ($resp['refreshExpiresAt'] ?? null);

  $_SESSION['user']=[
    'email'=>$who['email']??null,
    'role'=>'bayi',
    'partner_id'=>$who['partner_id']??null,
    'account_status'=>$resp['account_status']??null,
    'id'=>$who['id']??null,
  ];
  $_SESSION['email']=$_SESSION['user']['email'];
  $_SESSION['role'] =$_SESSION['user']['role'];
  session_regenerate_id(true);
}

/* =========================================================
   JSON REGISTER HANDLER (aynı dosyada) – ZORUNLU MFA (email|sms)
========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $ct = $_SERVER['CONTENT_TYPE'] ?? ($_SERVER['HTTP_CONTENT_TYPE'] ?? '');
  if (stripos($ct, 'application/json') !== false) {
    header('Content-Type: application/json; charset=utf-8');
    try {
      $in = json_decode(file_get_contents('php://input'), true) ?: [];

      // --- MFA kanalı: UI'dan gelen seçime göre (email | sms) ---
      $mfaMethod = strtolower(trim((string)($in['mfa_method'] ?? 'email')));
      if (!in_array($mfaMethod, ['email','sms'], true)) {
        $mfaMethod = 'email';
      }

      // --- Telefonu normalize et (SMS seçilmişse zorunlu) ---
      $phoneRaw = trim((string)($in['phone'] ?? ''));
      $phone    = preg_replace('~\D~','', $phoneRaw);
      if ($mfaMethod === 'sms') {
        if ($phone === '') {
          throw new Exception('SMS ile MFA için telefon zorunludur.');
        }
        // Basit TR normalize: 5XXXXXXXXX → 90 5XXXXXXXXX
        if (strpos($phone, '90') !== 0 && strlen($phone) === 10 && $phone[0] === '5') {
          $phone = '90' . $phone;
        }
      }

      // Backend payload
      $payload = [
        'type'        => $in['type']       ?? 'Bayi',
        'legal_type'  => $in['legal_type'] ?? 'Sirket',
        'email'       => mb_strtolower(trim((string)($in['email'] ?? '')),'UTF-8'),
        'password'    => (string)($in['password'] ?? ''),
        'phone'       => ($mfaMethod === 'sms') ? $phone : $phoneRaw,
        'address'     => trim((string)($in['address'] ?? '')),
        'mfa_method'  => $mfaMethod,
      ];

      if ($payload['legal_type'] === 'Sirket') {
        $payload['company_name'] = trim((string)($in['company_name'] ?? ''));
        $payload['vkn']          = preg_replace('~\D~','', (string)($in['vkn'] ?? ''));
        if (!empty($in['tax_office'])) $payload['tax_office'] = trim((string)$in['tax_office']);
      } else {
        $payload['first_name'] = trim((string)($in['first_name'] ?? ''));
        $payload['last_name']  = trim((string)($in['last_name']  ?? ''));
        $payload['tckn']       = preg_replace('~\D~','', (string)($in['tckn'] ?? ''));
      }

      // Register çağrısı
      $resp = api_post_json('register', $payload);

      // --- Her durumda ZORUNLU VERIFY ekranına gönder ---
      $_SESSION['mfa'] = [
        'session_id' => $resp['mfa']['session_id'] ?? ($resp['session_id'] ?? null),
        'method'     => $mfaMethod,  // email | sms
        'expires_at' => $resp['mfa']['expires_at'] ?? null,
        'forced'     => true,
        'prefill'    => [
          'scope'      => 'partner',
          'role'       => 'bayi',
          'email'      => $payload['email'],
          'phone'      => ($mfaMethod === 'sms') ? $payload['phone'] : null,
          'partner_id' => $resp['auth']['partner_id'] ?? null,
        ],
      ];

      echo json_encode([
        'success'         => true,
        'verify_required' => true,
        'method'          => $mfaMethod,
        'next'            => 'verify',
        'message'         => $resp['message'] ?? ($mfaMethod === 'sms'
                                ? 'Kayıt oluşturuldu. SMS ile gönderilen doğrulama kodunu girin.'
                                : 'Kayıt oluşturuldu. E-postanıza gönderilen doğrulama kodunu girin.'),
        'redirect'        => url('auth/verify.php'),
        'dev_otp'         => $resp['dev_otp'] ?? null,
      ], JSON_UNESCAPED_UNICODE);
      exit;

    } catch (Throwable $e) {
      http_response_code(400);
      echo json_encode([
        'success'=>false,
        'message'=>$e->getMessage() ?: 'Kayıt başarısız.',
      ], JSON_UNESCAPED_UNICODE);
      exit;
    }
  }
}

/* ---------- Panel yönlendirme ---------- */
function redirect_bayi(){ safe_redirect(url('bayi/bayi.php')); }

/* ---------- Zaten login ise /me ile sadece BAYİ'ye gönder ---------- */
if (!empty($_SESSION['accessToken'])) {
  try {
    $me = api_me($_SESSION['accessToken']);
    if (($me['userType'] ?? null) === 'partner') {
      redirect_bayi();
    } else {
      // Bu panel sadece bayi'ye açık; diğer tiplerde oturumu temizle
      $_SESSION = [];
      if (session_id()) session_destroy();
      session_start();
    }
  } catch(Throwable $e){ /* token bozuksa devam */ }
}

$error=null;

/* ======================================================
  POST (Form): Sadece BAYİ girişi yapılacak
====================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Bu blok sadece form-encoded login içindir (JSON ise yukarıda exit edildi)
  $email    = trim($_POST['email'] ?? '');
  $password = (string)($_POST['password'] ?? '');
  if ($email === '' || $password === '') {
    $error = 'E-posta ve şifre zorunludur.';
  } else {
    try {
      $resp = api_login_partner($email, $password);

      if (resp_requires_mfa($resp)) {
        $_SESSION['mfa'] = [
          'session_id' => $resp['mfa']['session_id'] ?? null,
          'method'     => $resp['mfa']['method'] ?? 'email',
          'expires_at' => $resp['mfa']['expires_at'] ?? null,
          'prefill'    => [
            'scope'      => 'partner',
            'email'      => $resp['auth']['email'] ?? $email,
            'partner_id' => $resp['auth']['partner_id'] ?? null,
            'role'       => 'bayi',
          ],
        ];
        safe_redirect(url('auth/verify.php'));
      }

      // token geldiyse güvenli şekilde bayi paneline
      save_tokens_and_user($resp, ['email' => $email, 'role' => 'bayi']);

      // ek kontrol: /me partner mi?
      try {
        $me = api_me($_SESSION['accessToken']);
        if (($me['userType'] ?? null) !== 'partner') {
          // güvenlik için iptal edip uyaralım
          $_SESSION = [];
          if (session_id()) session_destroy();
          session_start();
          $error = 'Bu kullanıcı bayi hesabı değil.';
        } else {
          redirect_bayi();
        }
      } catch (Throwable $em) {
        redirect_bayi();
      }

    } catch (Throwable $ePartner) {
      $error = $ePartner->getMessage() ?: 'Giriş başarısız.';
      _dbg('ERROR: ' . $error);
    }
  }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Giriş & Kayıt - Bayi Paneli</title>

  <!-- Tailwind CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Küçük ek CSS -->
  <link rel="stylesheet" href="<?= asset_url('custom.css') ?>">
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-2 sm:p-4"
      data-base="<?= htmlspecialchars(BASE, ENT_QUOTES, 'UTF-8') ?>">

  <div class="w-full max-w-sm sm:max-w-md lg:max-w-lg xl:max-w-xl">
    <!-- Logo/Başlık -->
    <div class="text-center mb-6 sm:mb-8">
      <div class="w-16 h-16 sm:w-20 sm:h-20 bg-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 sm:mb-6">
        <svg class="w-8 h-8 sm:w-10 sm:h-10 text-white" fill="currentColor" viewBox="0 0 20 20">
          <path d="M10 2L3 7v11a1 1 0 001 1h3v-6h6v6h3a1 1 0 001-1V7l-7-5z"/>
        </svg>
      </div>
      <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Hoş Geldiniz</h1>
      <p class="text-sm sm:text-base text-gray-600">Bayi Yönetim Paneli</p>
    </div>

    <!-- Kart -->
    <div class="bg-white rounded-xl sm:rounded-2xl p-6 sm:p-8 card-shadow border border-gray-100">
      <!-- Tab -->
      <div class="flex mb-6 sm:mb-8 bg-gray-100 rounded-lg p-1">
        <button id="loginTab" class="flex-1 py-3 px-4 rounded-md text-blue-600 text-sm sm:text-base font-medium transition-all duration-200 bg-white shadow-sm">Giriş Yap</button>
        <button id="registerTab" class="flex-1 py-3 px-4 rounded-md text-gray-600 text-sm sm:text-base font-medium transition-all duration-200 hover:text-gray-900">Kayıt Ol</button>
      </div>

      <!-- GİRİŞ FORMU (Sadece BAYİ) -->
      <form id="loginForm" method="post" action="<?= htmlspecialchars(url('auth/login.php'), ENT_QUOTES) ?>" onsubmit="return validateLoginForm();" class="space-y-4">
        <?php if ($error): ?>
          <div class="text-red-500 text-sm mb-2"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <!-- E-posta -->
        <div>
          <label class="block text-gray-700 text-sm font-medium mb-2">E-posta / Kullanıcı Adı</label>
          <input type="text" id="loginEmail" name="email" required
            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-gray-900 placeholder-gray-500 input-focus focus:border-blue-500 focus:bg-white focus:outline-none transition-all duration-200 text-sm sm:text-base"
            placeholder="ornek@email.com" oninput="clearError('loginEmail')">
          <div id="loginEmail-error" class="text-red-500 text-xs mt-1 hidden">Bu alanın doldurulması zorunludur</div>
        </div>

        <!-- Şifre -->
        <div class="relative">
          <input type="password" id="loginPassword" name="password" required
            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-gray-900 placeholder-gray-500 input-focus focus:border-blue-500 focus:bg-white focus:outline-none transition-all duration-200 pr-12 text-sm sm:text-base"
            placeholder="••••••••" oninput="clearError('loginPassword')" autocomplete="current-password">
          <button type="button" onclick="togglePassword('loginPassword')"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
            aria-label="Şifre görünürlüğünü değiştir" aria-pressed="false">
            <!-- Açık göz -->
            <svg class="w-5 h-5 eye-open hidden pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
            </svg>
            <!-- Kapalı göz -->
            <svg class="w-5 h-5 eye-closed pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
            </svg>
          </button>
        </div>

        <button type="submit" class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-blue-700 transition-all duration-200 text-sm sm:text-base">
          Giriş Yap
        </button>
      </form>

      <!-- KAYIT FORMU -->
      <div id="registerForm" class="space-y-5 hidden">
        <div id="registerError" class="text-red-600 text-sm mb-2 hidden"></div>

        <div>
          <label class="block text-gray-700 text-sm font-medium mb-3">Kayıt Tipi</label>
          <div class="flex space-x-3">
            <button type="button" class="register-type-btn flex-1 py-3 px-4 bg-blue-50 text-blue-600 text-sm rounded-lg border border-blue-200 transition-all duration-200 hover:bg-blue-100 font-medium" data-type="sirket">Şirket</button>
            <button type="button" class="register-type-btn flex-1 py-3 px-4 bg-gray-50 text-gray-600 text-sm rounded-lg border border-gray-200 transition-all duration-200 hover:bg-gray-100 font-medium" data-type="sahis">Şahıs</button>
          </div>
        </div>

        <div id="companyFields" class="space-y-4">
          <div>
            <label class="block text-gray-700 text-sm font-medium mb-2">Şirket Adı *</label>
            <input type="text" id="companyName" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-gray-900 placeholder-gray-500 input-focus focus:border-blue-500 focus:bg-white focus:outline-none transition-all duration-200 text-sm sm:text-base" placeholder="ABC Şirketi Ltd. Şti." oninput="clearError('companyName')">
            <div id="companyName-error" class="text-red-500 text-xs mt-1 hidden">Bu alanın doldurulması zorunludur</div>
          </div>
          <div>
            <label class="block text-gray-700 text-sm font-medium mb-2">Vergi Numarası *</label>
            <input type="text" id="taxNumber" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-gray-900 placeholder-gray-500 input-focus focus:border-blue-500 focus:bg-white focus:outline-none transition-all duration-200 text-sm sm:text-base" placeholder="1234567890" oninput="clearError('taxNumber')">
            <div id="taxNumber-error" class="text-red-500 text-xs mt-1 hidden">Bu alanın doldurulması zorunludur</div>
          </div>
        </div>

        <div id="personalFields" class="space-y-4 hidden">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-gray-700 text-sm font-medium mb-2">Ad *</label>
              <input type="text" id="firstName" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-gray-900 placeholder-gray-500 input-focus focus:border-blue-500 focus:bg-white focus:outline-none transition-all duration-200 text-sm sm:text-base" placeholder="Adınız" oninput="clearError('firstName')">
              <div id="firstName-error" class="text-red-500 text-xs mt-1 hidden">Bu alanın doldurulması zorunludur</div>
            </div>
            <div>
              <label class="block text-gray-700 text-sm font-medium mb-2">Soyad *</label>
              <input type="text" id="lastName" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-gray-900 placeholder-gray-500 input-focus focus:border-blue-500 focus:bg-white focus:outline-none transition-all duration-200 text-sm sm:text-base" placeholder="Soyadınız" oninput="clearError('lastName')">
              <div id="lastName-error" class="text-red-500 text-xs mt-1 hidden">Bu alanın doldurulması zorunludur</div>
            </div>
          </div>
          <div>
            <label class="block text-gray-700 text-sm font-medium mb-2">TC Kimlik No *</label>
            <input type="text" id="tcNumber" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-gray-900 placeholder-gray-500 input-focus focus:border-blue-500 focus:bg-white focus:outline-none transition-all duration-200 text-sm sm:text-base" placeholder="12345678901" oninput="clearError('tcNumber')">
            <div id="tcNumber-error" class="text-red-500 text-xs mt-1 hidden">Bu alanın doldurulması zorunludur</div>
          </div>
        </div>

        <div>
          <label class="block text-gray-700 text-sm font-medium mb-2">E-posta *</label>
          <input type="email" id="registerEmail" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-gray-900 placeholder-gray-500 input-focus focus:border-blue-500 focus:bg-white focus:outline-none transition-all duration-200 text-sm sm:text-base" placeholder="ornek@email.com" oninput="clearError('registerEmail')">
          <div id="registerEmail-error" class="text-red-500 text-xs mt-1 hidden">Bu alanın doldurulması zorunludur</div>
        </div>

        <div>
          <label class="block text-gray-700 text-sm font-medium mb-2">Telefon *</label>
          <input type="tel" id="phone" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-gray-900 placeholder-gray-500 input-focus focus:border-blue-500 focus:bg-white focus:outline-none transition-all duration-200 text-sm sm:text-base" placeholder="+90 (555) 123 45 67" oninput="clearError('phone')">
          <div id="phone-error" class="text-red-500 text-xs mt-1 hidden">Bu alanın doldurulması zorunludur</div>
        </div>

        <!-- Adres -->
        <div>
          <label class="block text-gray-700 text-sm font-medium mb-2">Adres *</label>
          <textarea
            id="address"
            rows="2"
            autocomplete="street-address"
            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-gray-900 placeholder-gray-500 input-focus focus:border-blue-500 focus:bg-white focus:outline-none transition-all duration-200 text-sm sm:text-base"
            placeholder="Şirket adresi veya ikamet adresiniz"
            oninput="clearError('address')"
          ></textarea>
          <div id="address-error" class="text-red-500 text-xs mt-1 hidden">Bu alanın doldurulması zorunludur</div>
        </div>

        <!-- Güvenlik (MFA) Tercihi -->
        <div>
          <label class="block text-gray-700 text-sm font-medium mb-3">Güvenlik (MFA) *</label>
          <div class="flex items-center gap-4 text-sm">
            <label class="inline-flex items-center gap-2">
              <input type="radio" name="mfaPref" id="mfaEmail" value="email" required checked>
              <span>E-posta ile MFA</span>
            </label>
            <label class="inline-flex items-center gap-2">
              <input type="radio" name="mfaPref" id="mfaSms" value="sms" required>
              <span>SMS ile MFA</span>
            </label>
          </div>
          <div id="mfaPref-error" class="text-red-500 text-xs mt-1 hidden">Bu alanın doldurulması zorunludur</div>
        </div>

        <div>
          <label class="block text-gray-700 text-sm font-medium mb-2">Şifre *</label>
          <div class="relative">
            <input type="password" id="registerPassword" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-gray-900 placeholder-gray-500 input-focus focus:border-blue-500 focus:bg-white focus:outline-none transition-all duration-200 pr-12 text-sm sm:text-base" placeholder="••••••••" oninput="clearError('registerPassword')">
            <button type="button" onclick="togglePassword('registerPassword')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors" aria-label="Şifre görünürlüğünü değiştir" aria-pressed="false">
              <svg class="w-5 h-5 eye-open hidden pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
              <svg class="w-5 h-5 eye-closed pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7 a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243 M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path></svg>
            </button>
          </div>
          <div id="registerPassword-error" class="text-red-500 text-xs mt-1 hidden">Bu alanın doldurulması zorunludur</div>
        </div>

        <div>
          <label class="block text-gray-700 text-sm font-medium mb-2">Şifre Tekrar *</label>
          <div class="relative">
            <input type="password" id="confirmPassword" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-gray-900 placeholder-gray-500 input-focus focus:border-blue-500 focus:bg-white focus:outline-none transition-all duration-200 pr-12 text-sm sm:text-base" placeholder="••••••••" oninput="clearError('confirmPassword')">
            <button type="button" onclick="togglePassword('confirmPassword')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors" aria-label="Şifre görünürlüğünü değiştir" aria-pressed="false">
              <svg class="w-5 h-5 eye-open hidden pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
              <svg class="w-5 h-5 eye-closed pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7 a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243 M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path></svg>
            </button>
          </div>
          <div id="confirmPassword-error" class="text-red-500 text-xs mt-1 hidden">Bu alanın doldurulması zorunludur</div>
        </div>

        <!-- KVKK / Sözleşme -->
        <div class="space-y-4 pt-2">
          <div>
            <label class="flex items-start">
              <input type="checkbox" id="kvkkCheck" class="w-4 h-4 text-blue-600 bg-white border-gray-300 rounded focus:ring-blue-500 focus:ring-2 mt-1" onchange="clearError('kvkkCheck')">
              <span class="ml-3 text-sm text-gray-600 leading-relaxed">
                <a href="#" onclick="showKVKK()" class="text-blue-600 underline hover:text-blue-800">KVKK Aydınlatma Metni</a>'ni okudum ve kabul ediyorum. *
              </span>
            </label>
            <div id="kvkkCheck-error" class="text-red-500 text-xs mt-1 ml-7 hidden">Bu onayın verilmesi zorunludur</div>
          </div>
          <div>
            <label class="flex items-start">
              <input type="checkbox" id="contractCheck" class="w-4 h-4 text-blue-600 bg-white border-gray-300 rounded focus:ring-blue-500 focus:ring-2 mt-1" onchange="clearError('contractCheck')">
              <span class="ml-3 text-sm text-gray-600 leading-relaxed">
                <a href="#" onclick="showContract()" class="text-blue-600 underline hover:text-blue-800">Kullanım Sözleşmesi</a>'ni okudum ve kabul ediyorum. *
              </span>
            </label>
            <div id="contractCheck-error" class="text-red-500 text-xs mt-1 ml-7 hidden">Bu onayın verilmesi zorunludur</div>
          </div>
        </div>

        <button id="registerBtn" type="button" onclick="handleRegister()" class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-blue-700 transition-all duration-200 text-sm sm:text-base mt-6">
          Kayıt Ol
        </button>
      </div>
    </div>

    <!-- Alt bilgi -->
    <div class="text-center mt-8">
      <p class="text-gray-500 text-sm">
        © 2024 Tüm hakları saklıdır. |
        <a href="#" class="text-blue-600 hover:text-blue-800 underline">Gizlilik Politikası</a> |
        <a href="#" class="text-blue-600 hover:text-blue-800 underline">Destek</a>
      </p>
    </div>
  </div>

  <!-- Modal -->
  <div id="modalOverlay" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center p-4 z-50">
    <div class="bg-white rounded-2xl p-6 max-w-md w-full max-h-96 overflow-y-auto">
      <div class="flex justify-between items-center mb-4">
        <h3 id="modalTitle" class="text-xl font-bold text-gray-800"></h3>
        <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
      </div>
      <div id="modalContent" class="text-gray-600 text-sm leading-relaxed"></div>
      <button onclick="closeModal()" class="w-full mt-4 bg-indigo-600 text-white py-2 px-4 rounded-lg hover:bg-indigo-700 transition-colors">
        Kapat
      </button>
    </div>
  </div>

  <!-- Harici JS -->
  <script src="<?= asset_url('login.js') ?>"></script>

  <!-- Register JS (inline) – bu dosyanın kendisine POST JSON atar -->
  <script>
  /* ---- ufak yardımcılar ---- */
  window.openModal ||= function(title, html){
    const o = document.getElementById('modalOverlay');
    const t = document.getElementById('modalTitle');
    const c = document.getElementById('modalContent');
    if(!o||!t||!c){ alert((title?title+': ':'')+String(html||'').replace(/<[^>]+>/g,'')); return; }
    t.textContent = title || '';
    c.innerHTML   = html || '';
    o.classList.remove('hidden'); o.classList.add('flex');
  };
  window.closeModal = function(){
    const o = document.getElementById('modalOverlay');
    if(o){ o.classList.add('hidden'); o.classList.remove('flex'); }
  };
  window.escapeHtml ||= (s)=>String(s||'').replace(/[&<>"']/g, m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;' }[m]));
  window.showError ||= function(id,msg){
    const el = document.getElementById(id+'-error');
    if(el){ if(msg) el.textContent = msg; el.classList.remove('hidden'); }
  };
  window.clearError ||= function(id){
    const el = document.getElementById(id+'-error');
    if(el){ el.classList.add('hidden'); }
  };

  /* ---- Şirket / Şahıs toggle ---- */
  (function initRegisterTypeToggle(){
    const typeBtns = document.querySelectorAll('.register-type-btn'); // data-type="sirket" | "sahis"
    const companyFields  = document.getElementById('companyFields');
    const personalFields = document.getElementById('personalFields');

    function setActive(btn){
      typeBtns.forEach(b=>{
        b.classList.remove('bg-blue-50','text-blue-600','border-blue-200');
        b.classList.add('bg-gray-50','text-gray-600','border-gray-200');
      });
      btn.classList.add('bg-blue-50','text-blue-600','border-blue-200');
      btn.classList.remove('bg-gray-50','text-gray-600','border-gray-200');

      if (btn.dataset.type === 'sahis') {
        personalFields?.classList.remove('hidden');
        companyFields?.classList.add('hidden');
      } else {
        companyFields?.classList.remove('hidden');
        personalFields?.classList.add('hidden');
      }
    }

    const initiallyActive = Array.from(typeBtns).find(b => b.classList.contains('bg-blue-50')) || typeBtns[0];
    if (initiallyActive) setActive(initiallyActive);

    typeBtns.forEach(btn=>{
      btn.addEventListener('click', ()=> setActive(btn));
    });
  })();

  /* ---- Register gönder ---- */
  window.handleRegister = async function () {
    try {
      const activeTypeBtn = Array.from(document.querySelectorAll('.register-type-btn'))
        .find(b => b.classList.contains('bg-blue-50'));
      const regType = activeTypeBtn?.dataset?.type || 'sirket';       // 'sirket' | 'sahis'
      const legal_type = regType === 'sahis' ? 'Sahis' : 'Sirket';

      // MFA tercihi (DÜZELTİLDİ: sms seçiliyse 'sms', değilse 'email')
      const mfa_method = document.getElementById('mfaSms')?.checked ? 'sms' : 'email';

      // Ortak alanlar
      const email   = document.getElementById('registerEmail')?.value.trim() || '';
      const phone   = document.getElementById('phone')?.value.trim() || '';
      const address = document.getElementById('address')?.value.trim() || '';
      const pass1   = document.getElementById('registerPassword')?.value || '';
      const pass2   = document.getElementById('confirmPassword')?.value || '';

      let valid = true;
      if (!email){ showError('registerEmail'); valid=false; }
      if (!phone){ showError('phone'); valid=false; }
      if (!address){ showError('address'); valid=false; }
      if (!pass1){ showError('registerPassword'); valid=false; }
      if (!pass2){ showError('confirmPassword'); valid=false; }
      if (pass1 && pass2 && pass1 !== pass2){ showError('confirmPassword','Şifreler uyuşmuyor'); valid=false; }
      if (!document.getElementById('kvkkCheck')?.checked){ showError('kvkkCheck'); valid=false; }
      if (!document.getElementById('contractCheck')?.checked){ showError('contractCheck'); valid=false; }
      if (!valid) return;

      const payload = {
        type: 'Bayi',
        legal_type,
        email,
        password: pass1,
        phone,
        address,
        mfa_method
      };

      if (legal_type === 'Sirket') {
        const company_name = document.getElementById('companyName')?.value.trim() || '';
        const vkn = (document.getElementById('taxNumber')?.value || '').replace(/\D/g,'');
        if (!company_name){ showError('companyName'); return; }
        if (!/^\d{10}$/.test(vkn)){ showError('taxNumber','10 haneli VKN girin'); return; }
        payload.company_name = company_name;
        payload.vkn = vkn;
      } else {
        const first_name = document.getElementById('firstName')?.value.trim() || '';
        const last_name  = document.getElementById('lastName')?.value.trim() || '';
        const tckn       = (document.getElementById('tcNumber')?.value || '').replace(/\D/g,'');
        if (!first_name){ showError('firstName'); return; }
        if (!last_name){ showError('lastName'); return; }
        if (!/^\d{11}$/.test(tckn)){ showError('tcNumber','11 haneli TCKN girin'); return; }
        payload.first_name = first_name;
        payload.last_name  = last_name;
        payload.tckn       = tckn;
      }

      // BU DOSYANIN KENDİSİNE JSON POST (auth/login.php)
      const endpoint = window.location.pathname;
      const res = await fetch(endpoint, {
        method: 'POST',
        headers: { 'Content-Type':'application/json' },
        body: JSON.stringify(payload),
      });
      const data = await res.json().catch(()=> ({}));

      if (!res.ok || !data?.success) {
        openModal('Kayıt Hatası', `<p class="text-red-600">${escapeHtml(data?.message || `HTTP ${res.status}`)}</p>`);
        return;
      }

      if (data.next === 'verify' && data.redirect) {
        openModal('Doğrulama Gerekli', `
          <p>${escapeHtml(data.message || 'E-posta ile doğrulama kodu gönderildi.')}</p>
          ${data.dev_otp ? `<p class="mt-2 text-xs">DEV OTP: <b>${escapeHtml(String(data.dev_otp))}</b></p>` : ''}
          <p class="mt-3">Kod sayfasına yönlendiriliyorsunuz…</p>
        `);
        setTimeout(()=>{ location.href = data.redirect; }, 700);
        return;
      }

      openModal('Kayıt Başarılı', `
        <p>${escapeHtml(data.message || 'Kayıt oluşturuldu.')}</p>
        <p class="mt-3">Devam etmek için giriş yapabilirsiniz.</p>
      `);
      const loginTab = document.getElementById('loginTab');
      if (loginTab) setTimeout(()=> loginTab.click(), 800);

    } catch (err) {
      openModal('Kayıt Hatası', `<p class="text-red-600">${escapeHtml(err?.message || String(err))}</p>`);
    }
  };
  </script>

</body>
</html>
