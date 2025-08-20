<?php
session_start();

$BASE = '/is-ortaklar-paneli/';

if (isset($_SESSION['user']['role'])) {
  $role = $_SESSION['user']['role'];
  header('Location: ' . ($role === 'admin' ? $BASE.'admin/anasayfa.php' : $BASE.'bayi/bayi.php'));
  exit;
}

$error = null;


/** Basit API istemcisi */
function api_login(string $email, string $password): array {
  $url = 'http://34.44.194.247:3000/api/auth/login'; // gerekirse https yap
  $payload = json_encode([
    'email'    => $email,
    'password' => $password,
  ], JSON_UNESCAPED_UNICODE);

  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => [
      'Content-Type: application/json',
      'Accept: application/json',
    ],
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_TIMEOUT        => 20,
  ]);

  $raw    = curl_exec($ch);
  $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $err    = curl_error($ch);
  curl_close($ch);

  if ($raw === false) {
    throw new RuntimeException('Sunucuya ulaşılamadı: '.$err);
  }

  $json = json_decode($raw, true);
  if ($json === null && json_last_error() !== JSON_ERROR_NONE) {
    throw new RuntimeException('Geçersiz yanıt: '.$raw);
  }

  if ($status < 200 || $status >= 300) {
    $msg = $json['message'] ?? $json['error'] ?? ('Giriş başarısız (HTTP '.$status.')');
    // Alan bazlı hatalar varsa ilkini ekleyelim
    if (!empty($json['errors']) && is_array($json['errors'])) {
      foreach ($json['errors'] as $k => $arr) {
        if (!empty($arr[0])) { $msg .= ' - '.$arr[0]; break; }
      }
    }
    throw new RuntimeException($msg);
  }

  return $json;
}

/* Form POST geldiyse kontrol et */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email    = trim($_POST['email'] ?? '');
  $password = (string)($_POST['password'] ?? '');
  $userType = $_POST['user_type'] ?? null; // admin | bayi
  $mfa      = trim($_POST['mfa'] ?? '');

  if ($email === '' || $password === '') {
    $error = 'E-posta ve şifre zorunludur.';
  } else {
    try {
        $resp = api_login($email, $password);

        // API rolü -> proje içi rol eşleme
        $apiRole = $resp['user']['role'] ?? 'partner_user';
        $normalizedRole = ($apiRole === 'admin') ? 'admin' : 'bayi';

        // *** BURASI KRİTİK ***
        // Kullanıcının seçtiği sekme ile API'den gelen rol eşleşmeli
        if ($normalizedRole !== $userType) {
            throw new RuntimeException(
                $normalizedRole === 'admin'
                  ? 'Bu hesap ADMIN rolündedir. Admin sekmesinden giriş yapmalısınız.'
                  : 'Bu hesap BAYİ rolündedir. Bayi sekmesinden giriş yapmalısınız.'
            );
        }

        // Oturum set et
        $_SESSION['accessToken']      = $resp['accessToken']      ?? null;
        $_SESSION['refreshToken']     = $resp['refreshToken']     ?? null;
        $_SESSION['sessionId']        = $resp['sessionId']        ?? null;
        $_SESSION['refreshExpiresAt'] = $resp['refreshExpiresAt'] ?? null;

        $_SESSION['user'] = [
            'id'         => $resp['user']['id']         ?? null,
            'email'      => $resp['user']['email']      ?? $email,
            'role'       => $normalizedRole,
            'partner_id' => $resp['user']['partner_id'] ?? null,
            'is_active'  => $resp['user']['is_active']  ?? null,
        ];

        $_SESSION['email'] = $_SESSION['user']['email'];
        $_SESSION['role']  = $_SESSION['user']['role'];

        session_regenerate_id(true);

        // Doğru sekmeden girilmişse yönlendir
        header('Location: ' . ($normalizedRole === 'admin' ? $BASE.'admin/anasayfa.php' : $BASE.'bayi/bayi.php'));
        exit;

    } catch (Throwable $ex) {
        $error = $ex->getMessage() ?: 'Giriş başarısız.';
    }
  }
}


?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Giriş & Kayıt - Admin/Bayi Paneli</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .input-focus:focus { box-shadow: 0 0 0 3px rgba(59,130,246,.1); }
    .card-shadow { box-shadow: 0 20px 25px -5px rgba(0,0,0,.1), 0 10px 10px -5px rgba(0,0,0,.04); }
  </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-2 sm:p-4">
  <div class="w-full max-w-sm sm:max-w-md lg:max-w-lg xl:max-w-xl">
    <!-- Logo/Başlık -->
    <div class="text-center mb-6 sm:mb-8">
      <div class="w-16 h-16 sm:w-20 sm:h-20 bg-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 sm:mb-6">
        <svg class="w-8 h-8 sm:w-10 sm:h-10 text-white" fill="currentColor" viewBox="0 0 20 20">
          <path d="M10 2L3 7v11a1 1 0 001 1h3v-6h6v6h3a1 1 0 001-1V7l-7-5z"/>
        </svg>
      </div>
      <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Hoş Geldiniz</h1>
      <p class="text-sm sm:text-base text-gray-600">Admin ve Bayi Yönetim Paneli</p>
    </div>

    <!-- Ana Form Kartı -->
    <div class="bg-white rounded-xl sm:rounded-2xl p-6 sm:p-8 card-shadow border border-gray-100">
      <!-- Tab Seçimi -->
      <div class="flex mb-6 sm:mb-8 bg-gray-100 rounded-lg p-1">
        <button id="loginTab" class="flex-1 py-3 px-4 rounded-md text-blue-600 text-sm sm:text-base font-medium transition-all duration-200 bg-white shadow-sm">Giriş Yap</button>
        <button id="registerTab" class="flex-1 py-3 px-4 rounded-md text-gray-600 text-sm sm:text-base font-medium transition-all duration-200 hover:text-gray-900">Kayıt Ol</button>
      </div>

      <!-- GİRİŞ FORMU -->
      <form id="loginForm" method="post" action="login.php" onsubmit="return validateLoginForm();" class="space-y-4">
        <?php if ($error): ?>
          <div class="text-red-500 text-sm mb-2"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <!-- Giriş Tipi -->
        <div>
          <label class="block text-gray-700 text-sm font-medium mb-3">Giriş Tipi</label>
          <div class="flex space-x-3">
            <button type="button"
              class="user-type-btn flex-1 py-3 px-4 bg-blue-50 text-blue-600 text-sm rounded-lg border border-blue-200 transition-all duration-200 hover:bg-blue-100 font-medium"
              data-type="admin">Admin</button>
            <button type="button"
              class="user-type-btn flex-1 py-3 px-4 bg-gray-50 text-gray-600 text-sm rounded-lg border border-gray-200 transition-all duration-200 hover:bg-gray-100 font-medium"
              data-type="bayi">Bayi</button>
          </div>
        </div>

        <!-- E-posta/Kullanıcı Adı -->
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
            <!-- Açık göz (şifre GÖRÜNÜRKEN gösterilecek) -->
            <svg class="w-5 h-5 eye-open hidden pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
            </svg>
            <!-- Kapalı göz (şifre GİZLİYKEN gösterilecek) -->
            <svg class="w-5 h-5 eye-closed pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
            </svg>
          </button>
      </div>
      
        <!-- Şifremi Unuttum & Beni Hatırla -->
        <div class="flex items-center justify-between">
          <label class="flex items-center">
            <input type="checkbox" class="w-4 h-4 text-blue-600 bg-white border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
            <span class="ml-2 text-sm text-gray-600">Beni Hatırla</span>
          </label>
          <button type="button" onclick="showForgotPassword()" class="text-sm text-blue-600 hover:text-blue-800 transition-colors underline">Şifremi Unuttum</button>
        </div>


        <!-- GİZLİ ROLE -->
        <input type="hidden" id="userTypeInput" name="user_type" value="admin">

        <!-- Giriş Butonu (SUBMIT) -->
        <button type="submit" class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-blue-700 transition-all duration-200 text-sm sm:text-base">
          Giriş Yap
        </button>
      </form>

      <!-- KAYIT FORMU (ENTEGRE) -->
      <div id="registerForm" class="space-y-5 hidden">
        <!-- Genel kayıt hatası -->
        <div id="registerError" class="text-red-600 text-sm mb-2 hidden"></div>

        <!-- Şirket/Şahıs Seçimi -->
        <div>
          <label class="block text-gray-700 text-sm font-medium mb-3">Kayıt Tipi</label>
          <div class="flex space-x-3">
            <button type="button" class="register-type-btn flex-1 py-3 px-4 bg-blue-50 text-blue-600 text-sm rounded-lg border border-blue-200 transition-all duration-200 hover:bg-blue-100 font-medium" data-type="sirket">Şirket</button>
            <button type="button" class="register-type-btn flex-1 py-3 px-4 bg-gray-50 text-gray-600 text-sm rounded-lg border border-gray-200 transition-all duration-200 hover:bg-gray-100 font-medium" data-type="sahis">Şahıs</button>
          </div>
        </div>

        <!-- Şirket Bilgileri -->
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

        <!-- Kişisel Bilgileri -->
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

        <!-- Ortak Alanlar -->
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

        <div>
  <label class="block text-gray-700 text-sm font-medium mb-2">Şifre *</label>
  <div class="relative">
    <input type="password" id="registerPassword"
      class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-gray-900
             placeholder-gray-500 input-focus focus:border-blue-500 focus:bg-white
             focus:outline-none transition-all duration-200 pr-12 text-sm sm:text-base"
      placeholder="••••••••" oninput="clearError('registerPassword')">
    <button type="button" onclick="togglePassword('registerPassword')"
      class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
      aria-label="Şifre görünürlüğünü değiştir" aria-pressed="false">
      <!-- Açık göz -->
      <svg class="w-5 h-5 eye-open hidden pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943
             9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
      </svg>
      <!-- Kapalı göz -->
      <svg class="w-5 h-5 eye-closed pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7
             a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243
             M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
      </svg>
    </button>
  </div>
  <div id="registerPassword-error" class="text-red-500 text-xs mt-1 hidden">
    Bu alanın doldurulması zorunludur
  </div>
</div>


   <div>
  <label class="block text-gray-700 text-sm font-medium mb-2">Şifre Tekrar *</label>
  <div class="relative">
    <input type="password" id="confirmPassword"
      class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-gray-900
             placeholder-gray-500 input-focus focus:border-blue-500 focus:bg-white
             focus:outline-none transition-all duration-200 pr-12 text-sm sm:text-base"
      placeholder="••••••••" oninput="clearError('confirmPassword')">
    <button type="button" onclick="togglePassword('confirmPassword')"
      class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
      aria-label="Şifre görünürlüğünü değiştir" aria-pressed="false">
      <!-- Açık göz -->
      <svg class="w-5 h-5 eye-open hidden pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943
             9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
      </svg>
      <!-- Kapalı göz -->
      <svg class="w-5 h-5 eye-closed pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7
             a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243
             M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
      </svg>
    </button>
  </div>
  <div id="confirmPassword-error" class="text-red-500 text-xs mt-1 hidden">
    Bu alanın doldurulması zorunludur
  </div>
</div>


        <!-- Onaylar -->
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
          <label class="flex items-start">
            <input type="checkbox" class="w-4 h-4 text-blue-600 bg-white border-gray-300 rounded focus:ring-blue-500 focus:ring-2 mt-1">
            <span class="ml-3 text-sm text-gray-600 leading-relaxed">Kampanya ve promosyon bilgilendirmelerini almak istiyorum.</span>
          </label>
        </div>

        <!-- Kayıt Butonu -->
        <button id="registerBtn" type="button" onclick="handleRegister()" class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-blue-700 transition-all duration-200 text-sm sm:text-base mt-6">
            Kayıt Ol
        </button>
      </div>
    </div>

    <!-- Alt Bilgi -->
    <div class="text-center mt-8">
      <p class="text-gray-500 text-sm">
        © 2024 Tüm hakları saklıdır. |
        <a href="#" class="text-blue-600 hover:text-blue-800 underline">Gizlilik Politikası</a> |
        <a href="#" class="text-blue-600 hover:text-blue-800 underline">Destek</a>
      </p>
    </div>
  </div>

  <!-- Modal Overlay -->
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

  <script>
  let selectedUserType = 'admin';
  let selectedRegisterType = 'sirket';

  // Tab değiştirme
  document.getElementById('loginTab').addEventListener('click', showLoginForm);
  document.getElementById('registerTab').addEventListener('click', showRegisterForm);

  function showLoginForm() {
    document.getElementById('loginTab').classList.add('bg-white','shadow-sm','text-blue-600');
    document.getElementById('loginTab').classList.remove('text-gray-600');
    document.getElementById('registerTab').classList.remove('bg-white','shadow-sm','text-blue-600');
    document.getElementById('registerTab').classList.add('text-gray-600');
     document.getElementById('loginForm').classList.remove('hidden');
    document.getElementById('registerForm').classList.add('hidden');
  }
  function showRegisterForm() {
    document.getElementById('registerForm').classList.remove('hidden');
    document.getElementById('loginForm').classList.add('hidden');
    document.getElementById('registerTab').classList.add('bg-white','shadow-sm','text-blue-600');
    document.getElementById('registerTab').classList.remove('text-gray-600');
    document.getElementById('loginTab').classList.remove('bg-white','shadow-sm','text-blue-600');
    document.getElementById('loginTab').classList.add('text-gray-600');
  }

  // Kullanıcı tipi seçimi
  document.querySelectorAll('.user-type-btn').forEach(btn => {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      selectedUserType = this.dataset.type;
      const hidden = document.getElementById('userTypeInput');
      if (hidden) hidden.value = selectedUserType;

      document.querySelectorAll('.user-type-btn').forEach(b => {
        b.classList.remove('bg-blue-50','text-blue-600','border-blue-200');
        b.classList.add('bg-gray-50','text-gray-600','border-gray-200');
      });
      this.classList.add('bg-blue-50','text-blue-600','border-blue-200');
      this.classList.remove('bg-gray-50','text-gray-600','border-gray-200');
    });
  });

  // Kayıt tipi seçimi
  document.querySelectorAll('.register-type-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      selectedRegisterType = this.dataset.type;
      document.querySelectorAll('.register-type-btn').forEach(b => {
        b.classList.remove('bg-blue-50','text-blue-600','border-blue-200');
        b.classList.add('bg-gray-50','text-gray-600','border-gray-200');
      });
      this.classList.add('bg-blue-50','text-blue-600','border-blue-200');
      this.classList.remove('bg-gray-50','text-gray-600','border-gray-200');

      if (selectedRegisterType === 'sirket') {
        document.getElementById('companyFields')?.classList.remove('hidden');
        document.getElementById('personalFields')?.classList.add('hidden');
      } else {
        document.getElementById('companyFields')?.classList.add('hidden');
        document.getElementById('personalFields')?.classList.remove('hidden');
      }
    });
  });

// Şifre görünürlüğü
function togglePassword(inputId) {
  const input = document.getElementById(inputId);
  if (!input) return;
  const button = input.nextElementSibling;
  if (!button) return;

  const eyeOpen   = button.querySelector('.eye-open');
  const eyeClosed = button.querySelector('.eye-closed');

  const show = input.type === 'password'; // şimdi gösterecek miyiz?
  input.type = show ? 'text' : 'password';

  // ikonları değiştir
  if (eyeOpen && eyeClosed) {
    if (show) {
      eyeOpen.classList.remove('hidden');   // açık göz
      eyeClosed.classList.add('hidden');    // kapalı göz
    } else {
      eyeOpen.classList.add('hidden');
      eyeClosed.classList.remove('hidden');
    }
  }

  // erişilebilirlik
  button.setAttribute('aria-pressed', show ? 'true' : 'false');
}

  // MFA toggle
  function toggleMFA() {
    const mfaSection = document.getElementById('mfaSection');
    const checkbox = document.getElementById('enableMFA');
    if (checkbox.checked) mfaSection.classList.remove('hidden'); else mfaSection.classList.add('hidden');
  }

  // Hata yardımcıları
  function clearError(fieldId) {
    const errorDiv = document.getElementById(fieldId + '-error');
    const inputField = document.getElementById(fieldId);
    if (errorDiv) errorDiv.classList.add('hidden');
    if (inputField) {
      inputField.classList.remove('border-red-500','bg-red-50');
      inputField.classList.add('border-gray-200','bg-gray-50');
    }
  }
  function showError(fieldId, message='Bu alanın doldurulması zorunludur') {
    const errorDiv = document.getElementById(fieldId + '-error');
    const inputField = document.getElementById(fieldId);
    if (errorDiv) { errorDiv.textContent = message; errorDiv.classList.remove('hidden'); }
    if (inputField) {
      inputField.classList.add('border-red-500','bg-red-50');
      inputField.classList.remove('border-gray-200','bg-gray-50');
    }
  }

  // Giriş formu doğrulama
  function validateLoginForm() {
    let isValid = true;
    const email = document.getElementById('loginEmail').value.trim();
    const password = document.getElementById('loginPassword').value;
    const mfaEnabled = document.getElementById('enableMFA').checked;
    if (!email) { showError('loginEmail'); isValid = false; }
    if (!password) { showError('loginPassword'); isValid = false; }
    if (mfaEnabled) {
      const mfaCode = document.getElementById('mfaCode').value.trim();
      if (!mfaCode) { showError('mfaCode'); isValid = false; }
    }
    return isValid;
  }

  // Kayıt formu doğrulama
  function validateRegisterForm() {
    let isValid = true;

    if (selectedRegisterType === 'sirket') {
      const companyName = document.getElementById('companyName').value.trim();
      const taxNumber = document.getElementById('taxNumber').value.trim();
      if (!companyName) { showError('companyName'); isValid = false; }
      if (!taxNumber) { showError('taxNumber'); isValid = false; }
      else if (taxNumber.length !== 10 || !/^\d+$/.test(taxNumber)) { showError('taxNumber','Vergi numarası 10 haneli olmalıdır'); isValid = false; }
    } else {
      const firstName = document.getElementById('firstName').value.trim();
      const lastName  = document.getElementById('lastName').value.trim();
      const tcNumber  = document.getElementById('tcNumber').value.trim();
      if (!firstName) { showError('firstName'); isValid = false; }
      if (!lastName)  { showError('lastName');  isValid = false; }
      if (!tcNumber)  { showError('tcNumber');  isValid = false; }
      else if (tcNumber.length !== 11 || !/^\d+$/.test(tcNumber)) { showError('tcNumber','TC Kimlik No 11 haneli olmalıdır'); isValid = false; }
    }

    const email = document.getElementById('registerEmail').value.trim();
    const phone = document.getElementById('phone').value.trim();
    const password = document.getElementById('registerPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;

    if (!email) { showError('registerEmail'); isValid = false; }
    else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { showError('registerEmail','Geçerli bir e-posta adresi girin'); isValid = false; }

    if (!phone) { showError('phone'); isValid = false; }
    else if (!/^[\+]?[0-9\s\(\)\-]{10,}$/.test(phone)) { showError('phone','Geçerli bir telefon numarası girin'); isValid = false; }

    if (!password) { showError('registerPassword'); isValid = false; }
    else if (password.length < 6) { showError('registerPassword','Şifre en az 6 karakter olmalıdır'); isValid = false; }

    if (!confirmPassword) { showError('confirmPassword'); isValid = false; }
    if (password && confirmPassword && password !== confirmPassword) { showError('confirmPassword','Şifreler eşleşmiyor'); isValid = false; }

    const kvkkCheck = document.getElementById('kvkkCheck').checked;
    const contractCheck = document.getElementById('contractCheck').checked;
    if (!kvkkCheck) { showError('kvkkCheck','Bu onayın verilmesi zorunludur'); isValid = false; }
    if (!contractCheck) { showError('contractCheck','Bu onayın verilmesi zorunludur'); isValid = false; }

    return isValid;
  }

  /* =============================
     REGISTER: GERÇEK API ENTEGRASYONU
     ============================= */

  // Frontend -> aynı origin proxy (CORS yok)
  const BASE_PATH = '<?= $BASE ?>'; // örn: '/is-ortaklar-paneli/'
  const ENDPOINTS = {
    company:    BASE_PATH + 'api/register-proxy.php?target=company',
    individual: BASE_PATH + 'api/register-proxy.php?target=individual'
  };

  // Yardımcılar
  function v(id){ return (document.getElementById(id)?.value || '').trim(); }
  function checked(id){ return !!document.getElementById(id)?.checked; }

  function showRegisterError(msg){
    const box = document.getElementById('registerError');
    if (!box) { alert(msg); return; }
    box.innerHTML = msg; // HTML liste gösterebilmek için
    box.classList.remove('hidden');
  }
  function clearRegisterError(){
    const box = document.getElementById('registerError');
    if (box) box.classList.add('hidden');
  }

  async function postJSON(url, data) {
    const res = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify(data)
    });

    // Hem JSON hem düz metin yanıtları güvenle parse et
    let raw = '';
    try { raw = await res.text(); } catch (_) {}
    let body = null;
    if (raw) {
      try { body = JSON.parse(raw); } catch (_) { body = { message: raw }; }
    }

    if (!res.ok) {
      const fieldErrors = body && body.errors;
      const firstFieldError = fieldErrors ? Object.values(fieldErrors).flat()[0] : null;

      const msg =
        (body && (body.message || body.error)) ||
        firstFieldError ||
        `İstek başarısız (${res.status})`;

      const err = new Error(msg);
      err.status = res.status;
      err.body = body;
      throw err;
    }
    return body;
  }

  function applyFieldErrors(errors){
    if (!errors) return;
    if (errors.company_title) showError('companyName', errors.company_title[0] || 'Hatalı değer');
    if (errors.name)          showError('companyName', errors.name[0] || 'Hatalı değer');

    if (errors.tax_no) {
      if (selectedRegisterType === 'sirket') showError('taxNumber', errors.tax_no[0] || 'Hatalı değer');
      else                                   showError('tcNumber',  errors.tax_no[0] || 'Hatalı değer');
    }
    if (errors.person_first_name) showError('firstName', errors.person_first_name[0] || 'Hatalı değer');
    if (errors.person_last_name)  showError('lastName',  errors.person_last_name[0]  || 'Hatalı değer');

    if (errors.email)            showError('registerEmail', errors.email[0] || 'Hatalı e-posta');
    if (errors.phone)            showError('phone', errors.phone[0] || 'Hatalı telefon');
    if (errors.password)         showError('registerPassword', errors.password[0] || 'Hatalı şifre');
    if (errors.password_confirm) showError('confirmPassword', errors.password_confirm[0] || 'Eşleşmiyor');
    if (errors.kvkk_accepted)    showError('kvkkCheck', errors.kvkk_accepted[0] || 'Zorunlu');
  }

  function summarizeErrors(errors){
    if (!errors) return '';
    const lines = [];
    for (const [k,v] of Object.entries(errors)) {
      const text = Array.isArray(v) ? v.join(', ') : String(v);
      lines.push(`• <b>${k}</b>: ${text}`);
    }
    return lines.join('<br>');
  }

  function buildRegisterPayload() {
    if (selectedRegisterType === 'sirket') {
      const company = v('companyName');
      return {
        company_title: company,
        name: company,
        tax_no: v('taxNumber').replace(/\D/g,''),
        email: v('registerEmail'),
        phone: v('phone').replace(/\s/g,''),
        password: v('registerPassword'),
        password_confirm: v('confirmPassword'),
        kvkk_accepted: checked('kvkkCheck')
      };
    } else {
      return {
        person_first_name: v('firstName'),
        person_last_name:  v('lastName'),
        tax_no: v('tcNumber').replace(/\D/g,''),
        email: v('registerEmail'),
        phone: v('phone').replace(/\s/g,''),
        password: v('registerPassword'),
        password_confirm: v('confirmPassword'),
        kvkk_accepted: checked('kvkkCheck')
      };
    }
  }

  async function handleRegister() {
    clearRegisterError();
    if (!validateRegisterForm()) return;

    const btn = document.getElementById('registerBtn');
    if (btn) { btn.disabled = true; btn.textContent = 'Gönderiliyor...'; }

    try {
      const type = (selectedRegisterType === 'sirket') ? 'company' : 'individual';
      const url  = ENDPOINTS[type];
      const payload = buildRegisterPayload();

      // console.log('POST ->', url, payload);
      const data = await postJSON(url, payload); // 201 beklenir

      showWaitingScreen(); // başarılı
    } catch (err) {
      let msg = err.message || 'Kayıt sırasında bir hata oluştu.';
      if (err.body?.errors) {
        applyFieldErrors(err.body.errors);
        msg += '<br>' + summarizeErrors(err.body.errors);
      }
      if (err.body?.error && !err.body?.errors) {
        msg = err.body.error;
      }
      showRegisterError(msg);
    } finally {
      if (btn) { btn.disabled = false; btn.textContent = 'Kayıt Ol'; }
    }
  }

  // Modal/helperlar (kayıt ekranı sonrası vs.)
  function showForgotPassword() {
    const email = prompt('Şifre sıfırlama bağlantısının gönderileceği e-posta adresinizi girin:');
    if (email) alert(`Şifre sıfırlama bağlantısı ${email} adresine gönderildi! (Demo)`);
  }
  function showKVKK() {
    document.getElementById('modalTitle').textContent = 'KVKK Aydınlatma Metni';
    document.getElementById('modalContent').innerHTML = `
      <p class="mb-3"><strong>Kişisel Verilerin Korunması Hakkında Bilgilendirme</strong></p>
      <p class="mb-2">6698 sayılı KVKK uyarınca, kişisel verileriniz ...</p>`;
    document.getElementById('modalOverlay').classList.remove('hidden');
    document.getElementById('modalOverlay').classList.add('flex');
  }
  function showContract() {
    document.getElementById('modalTitle').textContent = 'Kullanım Sözleşmesi';
    document.getElementById('modalContent').innerHTML = `
      <p class="mb-3"><strong>Hizmet Kullanım Şartları</strong></p>
      <p class="mb-2">Bu sözleşme ...</p>`;
    document.getElementById('modalOverlay').classList.remove('hidden');
    document.getElementById('modalOverlay').classList.add('flex');
  }
  function showWaitingScreen() {
    document.getElementById('modalTitle').textContent = 'Kayıt Başarılı!';
    document.getElementById('modalContent').innerHTML = `<p>Kaydınız alınmıştır. Yönetici onayından sonra giriş yapabilirsiniz.</p>`;
    const modalOverlay = document.getElementById('modalOverlay');
    const closeButton = modalOverlay.querySelector('button');
    closeButton.textContent = 'Tamam';
    closeButton.onclick = function(){ closeModal(); showLoginForm(); clearAllForms(); };
    modalOverlay.classList.remove('hidden'); modalOverlay.classList.add('flex');
  }
  function closeModal() {
    document.getElementById('modalOverlay').classList.add('hidden');
    document.getElementById('modalOverlay').classList.remove('flex');
  }
  document.getElementById('modalOverlay').addEventListener('click', function(e){ if (e.target === this) closeModal(); });

  function clearAllForms() {
    // Giriş formu
    document.getElementById('loginEmail').value = '';
    document.getElementById('loginPassword').value = '';
    document.getElementById('mfaCode').value = '';
    // Kayıt formu
    const registerInputs = document.querySelectorAll('#registerForm input[type="text"], #registerForm input[type="email"], #registerForm input[type="tel"], #registerForm input[type="password"]');
    registerInputs.forEach(input => input.value = '');
    const checkboxes = document.querySelectorAll('#registerForm input[type="checkbox"]');
    checkboxes.forEach(checkbox => checkbox.checked = false);
    // Hata mesajlarını temizle
    const errorDivs = document.querySelectorAll('[id$="-error"]'); errorDivs.forEach(d => d.classList.add('hidden'));
    // Input stillerini sıfırla
    const allInputs = document.querySelectorAll('input'); allInputs.forEach(i => { i.classList.remove('border-red-500','bg-red-50'); i.classList.add('border-gray-200','bg-gray-50'); });
    // Genel kayıt hata kutusu
    clearRegisterError();
  }
</script>
</body>
</html>