<?php
require_once __DIR__ . '/../config/config.php';

   //if (!isset($_SESSION['user'])) {
  //header('Location: ' . url('auth/login.php'));
  //exit;
  // }

// Örnek: e-posta adresini maskele
$email = $_SESSION['user']['email'] ?? '';
$maskedEmail = preg_replace('/(.).+(@.+)/', '$1***$2', $email);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1" />
  <title>E‑posta Doğrulama</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>

    /* Tüm öğelerde doğru boyut hesaplama */
    *, *::before, *::after { box-sizing: border-box; }

    /* Tam ekran ve kesinlikle kaydırma yok */
    html, body { height: 100%; }
    body { overflow: hidden; }

    .gradient-bg {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .card-shadow {
      box-shadow:
        0 20px 25px -5px rgba(0, 0, 0, 0.1),
        0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    .success-message { animation: slideIn 0.3s ease-out; }
    .error-message   { animation: shake 0.5s ease-in-out; }
    @keyframes slideIn {
      from { transform: translateY(-10px); opacity: 0; }
      to   { transform: translateY(0);    opacity: 1; }
    }
    @keyframes shake {
      0%,100% { transform: translateX(0); }
      25%     { transform: translateX(-5px); }
      75%     { transform: translateX(5px); }
    }

    /* YENİ */
    .otp-input {
    transition: box-shadow .15s ease, border-color .15s ease;
    font-size: 22px;          /* iOS zoom’u engellemek için min 16px+ */
    line-height: 1.2;
    }
    
    .otp-input:focus {
    outline: none;
    transform: none;          /* emniyet olsun diye */
    border-color: #3b82f6;    /* Tailwind blue-500 */
    box-shadow: 0 0 0 3px rgba(59,130,246,.15);
    }

    /* Kart yüksekliğini güvenli alana sığdır (üst-alt padding dahil) */
    .viewport-card {
      /* Ekran yüksekliği - dış boşluklar (16px + 16px) */
      max-height: calc(100vh - 32px);
      overflow: hidden; /* İçerik taşmasın */
    }

    /* Kart yüksekliği: tek ekrana sığsın ama taşanı da kırpmasın (ring dışarı taşmayacak zaten) */
    .viewport-card {
        max-height: calc(100vh - 32px); /* p-4 dış boşluğa denk */
        /* overflow: hidden;  <-- BUNU KALDIRDIK */
    }

    /* Kart içini esnet: başlık + form + alt bilgi, dikeyde sığsın */

    .card-body {
        display: grid;
        grid-template-rows: auto 1fr auto;
        row-gap: 16px;
        height: 100%;
        /* overflow: hidden;  <-- BUNU DA KALDIRDIK */
    }

    /* Metinler küçük ekranlarda taşmasın */

    .title-text { font-size: clamp(18px, 2.5vw, 22px); }
    .subtitle-text { font-size: clamp(12px, 2vw, 14px); }
    .footnote-text { font-size: clamp(11px, 1.8vw, 13px); }

    /* Tarayıcı outline'larını kapat, ring kullan */
    button:focus, button:focus-visible { outline: none; }
    button::-moz-focus-inner { border: 0; }
    button { -webkit-tap-highlight-color: transparent; }

    /* Input odak stilinde scale yok */
    .otp-input { transition: box-shadow .15s ease, border-color .15s ease; font-size: 22px; line-height: 1.2; }
    .otp-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.15); 

    /* İç alanlarda dikey taşma riskine karşı kaydırma YOK, sadece kırp */
    .no-scroll { overflow: hidden; }
  </style>
</head>
<body class="h-screen gradient-bg flex items-center justify-center p-4">

  <!-- Ana Container -->
  <div class="w-full max-w-md">
    <!-- OTP Card -->
    <div class="bg-white rounded-2xl card-shadow viewport-card p-6 sm:p-8">
      <div class="card-body">
        <!-- Header -->
        <div class="text-center no-scroll">
          <div class="w-14 h-14 sm:w-16 sm:h-16 bg-green-100 rounded-2xl flex items-center justify-center mx-auto mb-3 sm:mb-4">
            <svg class="w-7 h-7 sm:w-8 sm:h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 7.89a2 2 0 002.83 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
          </div>
          <h2 class="title-text font-bold text-gray-900 mb-1">E‑posta Doğrulama</h2>
          <p class="subtitle-text text-gray-600">
            <span id="emailDisplay" class="font-medium text-blue-600">d***o@example.com</span>
            adresine gönderilen 6 haneli kodu girin
          </p>
        </div>

        <!-- Form alanı -->
        <div class="space-y-4 no-scroll">
          <!-- OTP Input -->
          <div>
            <label for="otpCode" class="block text-sm font-medium text-gray-700 mb-2">Doğrulama Kodu</label>
            <input
              type="text" id="otpCode" name="otpCode" maxlength="6" required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-center text-2xl font-mono tracking-widest otp-input"
              placeholder="000000" oninput="formatOTPInput(this)"
            />
          </div>

          <!-- Info Box (tek satırlık, taşmaz) -->
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
            <div class="flex items-start">
              <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <p class="text-sm text-blue-800">
                Doğrulama kodu gönderildi. Kod 5 dakika geçerlidir. Spam klasörünü de kontrol edin.
              </p>
            </div>
          </div>

          <!-- Resend -->
          <div class="text-center">
            <p class="text-sm text-gray-600 mb-2">Kodu almadınız mı?</p>
            <button
              type="button" id="resendBtn" onclick="resendOTP()"
              class="text-blue-600 hover:text-blue-500 font-medium disabled:text-gray-400 disabled:cursor-not-allowed text-sm transition-colors"
            >
              <span id="resendText">Tekrar gönder</span>
              <span id="countdown" class="text-gray-500"></span>
            </button>
          </div>

          <!-- Actions -->
          <div class="flex gap-3">
            <button
              type="button" onclick="backToLogin()"
              class="flex-1 bg-gray-100 text-gray-700 py-3 rounded-lg hover:bg-gray-200 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors font-medium"
            >
              <span class="inline-flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Geri
              </span>
            </button>
            <button
              type="button" id="verifyBtn" onclick="verifyOTP()"
              class="flex-1 bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors font-medium"
            >
              <span id="verifyBtnText">Doğrula</span>
              <svg id="verifySpinner" class="hidden animate-spin -ml-1 mr-0.5 h-5 w-5 text-white inline" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </button>
          </div>
        </div>

        <!-- Footer (tek satır) -->
        <div class="text-center no-scroll">
          <p class="footnote-text text-gray-500">Güvenliğiniz için bu kod sadece 5 dakika geçerlidir</p>
        </div>
      </div>
    </div>

    <!-- Güvenlik Info (kart dışı, yine tek satır) -->
    <div class="mt-3 text-center">
      <div class="flex items-center justify-center text-white footnote-text">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
        <span>256‑bit SSL şifreleme ile korunmaktadır</span>
      </div>
    </div>
  </div>

  <!-- Mesaj tostu (ekrana sabit, taşma yapmaz) -->
  <div id="toastRoot" class="fixed top-4 right-4 z-50 space-y-2"></div>

  <script>
    // DEMO: backende bağlayana kadar
    let otpCode = '123456';
    let resendTimer = 0;
    let resendInterval = null;

    window.addEventListener('load', function () {
      document.getElementById('otpCode').focus();
      startResendTimer();
      // Konsol için demo kodu:
      console.log('Demo OTP Code:', otpCode);
    });

    async function verifyOTP() {
      const enteredCode = document.getElementById('otpCode').value.trim();

      if (!enteredCode || enteredCode.length !== 6) {
        showError('Lütfen 6 haneli doğrulama kodunu girin');
        return;
      }

      setVerifyLoading(true);
      try {
        // Burada backend’e POST edin:
        // const res = await fetch('/auth/verify.php', { method:'POST', body: new URLSearchParams({ otp: enteredCode }) });

        // Demo bekleme
        await new Promise(r => setTimeout(r, 800));

        if (enteredCode === otpCode) {
          showSuccess('Doğrulama başarılı! Yönlendiriliyorsunuz...');
          setTimeout(() => {
            // window.location.href = '/admin/anasayfa' veya '/bayi/bayi'
            alert('Başarılı doğrulama! Yönlendiriliyorsunuz…');
          }, 900);
        } else {
          showError('Doğrulama kodu hatalı. Lütfen tekrar deneyin.');
          document.getElementById('otpCode').value = '';
          document.getElementById('otpCode').focus();
        }
      } catch (e) {
        showError('Doğrulama sırasında bir hata oluştu');
      } finally {
        setVerifyLoading(false);
      }
    }

    async function resendOTP() {
      if (resendTimer > 0) return;
      try {
        // Backend: yeni kod üret + mail gönder
        // const res = await fetch('/auth/verify.php?resend=1');

        // Demo: yeni kod
        otpCode = String(Math.floor(100000 + Math.random() * 900000));
        console.log('New OTP Code:', otpCode);

        showSuccess('Yeni doğrulama kodu gönderildi');
        document.getElementById('otpCode').value = '';
        document.getElementById('otpCode').focus();
        startResendTimer();
      } catch (e) {
        showError('Kod gönderilirken hata oluştu');
      }
    }

    function backToLogin() {
      // window.location.href = '/auth/login';
      showSuccess('Giriş sayfasına dönülüyor…');
    }

    function startResendTimer() {
      resendTimer = 60;
      const resendBtn = document.getElementById('resendBtn');
      const countdownSpan = document.getElementById('countdown');

      resendBtn.disabled = true;
      resendBtn.classList.add('disabled:text-gray-400','disabled:cursor-not-allowed');

      resendInterval = setInterval(() => {
        if (resendTimer > 0) {
          countdownSpan.textContent = ` (${resendTimer}s)`;
          resendTimer--;
        } else {
          clearResendTimer();
        }
      }, 1000);
    }

    function clearResendTimer() {
      if (resendInterval) { clearInterval(resendInterval); resendInterval = null; }
      resendTimer = 0;
      const resendBtn = document.getElementById('resendBtn');
      const countdownSpan = document.getElementById('countdown');
      resendBtn.disabled = false;
      resendBtn.classList.remove('disabled:text-gray-400','disabled:cursor-not-allowed');
      countdownSpan.textContent = '';
    }

    function formatOTPInput(input) {
      input.value = input.value.replace(/[^0-9]/g, '').slice(0,6);
      if (input.value.length === 6) setTimeout(() => verifyOTP(), 300);
    }

    function setVerifyLoading(loading) {
      const btn = document.getElementById('verifyBtn');
      const text = document.getElementById('verifyBtnText');
      const spinner = document.getElementById('verifySpinner');
      if (loading) {
        btn.disabled = true; text.textContent = 'Doğrulanıyor...'; spinner.classList.remove('hidden');
      } else {
        btn.disabled = false; text.textContent = 'Doğrula'; spinner.classList.add('hidden');
      }
    }

    // Toast helper (ekrana sabit; yükseklik etkilemez)
    function showSuccess(msg){ showToast(msg, true); }
    function showError(msg){ showToast(msg, false); }
    function showToast(message, ok=true){
      const root = document.getElementById('toastRoot');
      const el = document.createElement('div');
      el.className = `px-4 py-2 rounded-lg shadow-lg text-white text-sm ${ok?'bg-green-600 success-message':'bg-red-600 error-message'}`;
      el.textContent = message;
      root.appendChild(el);
      setTimeout(()=>{ el.remove(); }, 3500);
    }
  </script>
</body>
</html>
