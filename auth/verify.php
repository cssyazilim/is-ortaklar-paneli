<?php
// file: auth/verify.php
require_once __DIR__ . '/../config/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

/* ------------------ Konfig ------------------ */
ini_set('display_errors', 0);
error_reporting(E_ALL);

if (!defined('MFA_BASE')) {
  define('MFA_BASE', rtrim(API_BASE, '/') . '/mfa');
}

/* ------------------ Yardımcılar ------------------ */
function json_response(array $data, int $code=200): void {
  http_response_code($code);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($data, JSON_UNESCAPED_UNICODE);
  exit;
}
function safe_redirect(string $url,int $code=302):void{
  if (!headers_sent()) { header('Location: '.$url, true, $code); session_write_close(); exit; }
  echo '<script>location.href='.json_encode($url).';</script>';
  echo '<noscript><meta http-equiv="refresh" content="0;url='.htmlspecialchars($url,ENT_QUOTES,'UTF-8').'"></noscript>';
  session_write_close(); exit;
}
function http_post_json(string $base, string $path, array $body): array {
  $url = rtrim($base,'/').'/'.ltrim($path,'/');
  $payload = json_encode($body, JSON_UNESCAPED_UNICODE);

  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json','Accept: application/json'],
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_TIMEOUT        => 20,
  ]);
  $raw  = curl_exec($ch);
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $err  = curl_error($ch);
  curl_close($ch);

  if ($raw === false) throw new RuntimeException('Sunucuya ulaşılamadı: '.$err);
  $json = json_decode($raw, true);
  if (!is_array($json)) throw new RuntimeException('Geçersiz yanıt: '.$raw);
  if ($code < 200 || $code >= 300) {
    $msg = $json['message'] ?? $json['error'] ?? ('HTTP '.$code);
    throw new RuntimeException($msg);
  }
  return $json;
}

/* Token + kullanıcıyı session'a yaz (doğrulamadan sonra) */
function save_tokens_and_user_after_verify(array $resp): void {
  $_SESSION['accessToken']      = $resp['accessToken']        ?? ($resp['token'] ?? null);
  $_SESSION['refreshToken']     = $resp['refreshToken']       ?? null;
  $_SESSION['sessionId']        = $resp['sessionId']          ?? null;
  $_SESSION['refreshExpiresAt'] = $resp['refresh_expires_at'] ?? ($resp['refreshExpiresAt'] ?? null);

  $pref   = $_SESSION['mfa']['prefill'] ?? [];
  $email  = $resp['auth']['email'] ?? ($pref['email'] ?? ($_SESSION['email'] ?? null));
  $scope  = $resp['auth']['scope'] ?? ($pref['scope'] ?? 'partner'); // partner|user
  $role   = $pref['role'] ?? (($scope === 'partner') ? 'bayi' : 'admin');

  $_SESSION['user'] = [
    'email'         => $email,
    'role'          => $role,
    'scope'         => $scope,
    'partner_id'    => $resp['auth']['partner_id'] ?? ($pref['partner_id'] ?? null),
    'account_status'=> $resp['auth']['account_status'] ?? null,
  ];
  $_SESSION['email'] = $email;
  $_SESSION['role']  = $role;

  session_regenerate_id(true);
}

/* E-posta maskeleme */
function mask_email(?string $e): string {
  $e = trim((string)$e);
  if ($e === '' || strpos($e, '@') === false) return $e;
  [$local, $domain] = explode('@', $e, 2);
  $first = mb_substr($local, 0, 1, 'UTF-8');
  return $first.'***@'.$domain;
}

/* ------------------ AJAX POST (verify/resend) ------------------ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $ct = $_SERVER['CONTENT_TYPE'] ?? ($_SERVER['HTTP_CONTENT_TYPE'] ?? '');
  if (stripos($ct, 'application/json') !== false) {
    $in  = json_decode(file_get_contents('php://input'), true) ?: [];
    $sid = $_SESSION['mfa']['session_id'] ?? ($in['session_id'] ?? '');
    if (!$sid) json_response(['success'=>false,'message'=>'Doğrulama oturumu bulunamadı. Yeniden giriş yapın.'], 400);

    try {
      // 1) Doğrulama
      if (isset($in['code'])) {
        $code = preg_replace('~\D~','', (string)$in['code']);
        if ($code === '' || strlen($code) !== 6) {
          json_response(['success'=>false,'message'=>'Geçerli 6 haneli kod girin.'], 400);
        }

        // POST: /api/mfa/verify  -> { session_id, code }
        $resp = http_post_json(MFA_BASE, 'verify', ['session_id'=>$sid, 'code'=>$code]);

        save_tokens_and_user_after_verify($resp);

        // scope/role’a göre yönlendir
        $pref = $_SESSION['mfa']['prefill'] ?? [];
        $redirect = ($pref['scope'] ?? 'partner') === 'partner'
          ? url('bayi/index.php')
          : url('admin/anasayfa.php');

        unset($_SESSION['mfa']);

        json_response([
          'success'  => true,
          'message'  => $resp['message'] ?? 'Doğrulama başarılı.',
          'redirect' => $redirect
        ]);
      }

      // 2) Kodu yeniden gönder
      if (!empty($in['resend'])) {
        $method = $_SESSION['mfa']['method'] ?? 'email';
        // POST: /api/mfa/resend  -> { session_id, method }
        $resp = http_post_json(MFA_BASE, 'resend', ['session_id'=>$sid, 'method'=>$method]);
        json_response([
          'success'=>true,
          'message'=>$resp['message'] ?? 'Doğrulama kodu tekrar gönderildi.'
        ]);
      }

      json_response(['success'=>false,'message'=>'Geçersiz istek.'], 400);

    } catch (Throwable $e) {
      json_response(['success'=>false,'message'=>$e->getMessage() ?: 'İşlem başarısız.'], 400);
    }
  }
}

/* ------------------ Sayfa render ------------------ */
// MFA oturumu yoksa login'e geri
if (empty($_SESSION['mfa']['session_id'])) {
  safe_redirect(url('auth/login.php'));
}
$email = $_SESSION['mfa']['prefill']['email']
      ?? $_SESSION['email']
      ?? ($_SESSION['user']['email'] ?? '');
$maskedEmail = mask_email($email);
$sid         = $_SESSION['mfa']['session_id'];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1" />
  <title>E-posta Doğrulama</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    *,*::before,*::after{box-sizing:border-box}
    html,body{height:100%}
    body{overflow:hidden}
    .gradient-bg{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%)}
    .card-shadow{box-shadow:0 20px 25px -5px rgba(0,0,0,.1),0 10px 10px -5px rgba(0,0,0,.04)}
    .success-message{animation:slideIn .3s ease-out}
    .error-message{animation:shake .5s ease-in-out}
    @keyframes slideIn{from{transform:translateY(-10px);opacity:0}to{transform:translateY(0);opacity:1}}
    @keyframes shake{0%,100%{transform:translateX(0)}25%{transform:translateX(-5px)}75%{transform:translateX(5px)}}
    .otp-input{transition:box-shadow .15s ease,border-color .15s ease;font-size:22px;line-height:1.2}
    .otp-input:focus{outline:none;border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.15)}
    .viewport-card{max-height:calc(100vh - 32px)}
    .title-text{font-size:clamp(18px,2.5vw,22px)}
    .subtitle-text{font-size:clamp(12px,2vw,14px)}
    .footnote-text{font-size:clamp(11px,1.8vw,13px)}
    .no-scroll{overflow:hidden}
  </style>
</head>
<body class="h-screen gradient-bg flex items-center justify-center p-4"
      data-login-href="<?= htmlspecialchars(url('auth/login.php'), ENT_QUOTES) ?>"
      data-sid="<?= htmlspecialchars($sid, ENT_QUOTES) ?>">

  <div class="w-full max-w-md">
    <div class="bg-white rounded-2xl card-shadow viewport-card p-6 sm:p-8">
      <div class="grid" style="grid-template-rows:auto 1fr auto; row-gap:16px; height:100%;">
        <div class="text-center no-scroll">
          <div class="w-14 h-14 sm:w-16 sm:h-16 bg-green-100 rounded-2xl flex items-center justify-center mx-auto mb-3 sm:mb-4">
            <svg class="w-7 h-7 sm:w-8 sm:h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 7.89a2 2 0 002.83 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
          </div>
          <h2 class="title-text font-bold text-gray-900 mb-1">E-posta Doğrulama</h2>
          <p class="subtitle-text text-gray-600">
            <span class="font-medium text-blue-600"><?= htmlspecialchars($maskedEmail ?: 'e-posta', ENT_QUOTES, 'UTF-8') ?></span>
            adresine gönderilen 6 haneli kodu girin
          </p>
        </div>

        <div class="space-y-4 no-scroll">
          <div>
            <label for="otpCode" class="block text-sm font-medium text-gray-700 mb-2">Doğrulama Kodu</label>
            <input type="text" id="otpCode" maxlength="6" required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-center text-2xl font-mono tracking-widest otp-input"
              placeholder="000000" oninput="formatOTPInput(this)"/>
          </div>

          <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
            <div class="flex items-start">
              <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <p class="text-sm text-blue-800">Doğrulama kodu gönderildi. Kod 5 dakika geçerlidir. Spam klasörünü de kontrol edin.</p>
            </div>
          </div>

          <div class="text-center">
            <p class="text-sm text-gray-600 mb-2">Kodu almadınız mı?</p>
            <button type="button" id="resendBtn" onclick="resendOTP()"
              class="text-blue-600 hover:text-blue-500 font-medium disabled:text-gray-400 disabled:cursor-not-allowed text-sm transition-colors">
              <span id="resendText">Tekrar gönder</span>
              <span id="countdown" class="text-gray-500"></span>
            </button>
          </div>

          <div class="flex gap-3">
            <button type="button" onclick="backToLogin()"
              class="flex-1 bg-gray-100 text-gray-700 py-3 rounded-lg hover:bg-gray-200 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors font-medium">
              <span class="inline-flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Geri
              </span>
            </button>
            <button type="button" id="verifyBtn" onclick="verifyOTP()"
              class="flex-1 bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors font-medium">
              <span id="verifyBtnText">Doğrula</span>
              <svg id="verifySpinner" class="hidden animate-spin -ml-1 mr-0.5 h-5 w-5 text-white inline" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </button>
          </div>
        </div>

        <div class="text-center no-scroll">
          <p class="footnote-text text-gray-500">Güvenliğiniz için bu kod sadece 5 dakika geçerlidir</p>
        </div>
      </div>
    </div>

    <div class="mt-3 text-center">
      <div class="flex items-center justify-center text-white footnote-text">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
        <span>256-bit SSL şifreleme ile korunmaktadır</span>
      </div>
    </div>
  </div>

  <div id="toastRoot" class="fixed top-4 right-4 z-50 space-y-2"></div>

  <script>
    let resendTimer = 0, resendInterval = null;

    window.addEventListener('load', function(){
      document.getElementById('otpCode').focus();
      startResendTimer();
    });

    function formatOTPInput(input){
      input.value = input.value.replace(/[^0-9]/g,'').slice(0,6);
      if (input.value.length === 6) setTimeout(()=>verifyOTP(), 200);
    }

    async function verifyOTP(){
      const code = (document.getElementById('otpCode').value || '').trim();
      if (code.length !== 6){ showError('Lütfen 6 haneli doğrulama kodunu girin'); return; }

      const sid = document.body.dataset.sid || '';
      if (!sid){ showError('Doğrulama oturum bilgisi yok. Yeniden giriş yapın.'); return; }

      setVerifyLoading(true);
      try {
        const res = await fetch(location.pathname, {
          method: 'POST',
          headers: {'Content-Type':'application/json'},
          body: JSON.stringify({ session_id: sid, code })
        });
        const data = await res.json().catch(()=>({}));
        if (!res.ok || !data?.success) throw new Error(data?.message || `HTTP ${res.status}`);

        showSuccess(data.message || 'Doğrulama başarılı.');
        setTimeout(()=>{ location.href = data.redirect || '<?= e(url("bayi/index.php")) ?>'; }, 500);
      } catch (e) {
        showError(e.message || 'Doğrulama başarısız.');
        document.getElementById('otpCode').value = '';
        document.getElementById('otpCode').focus();
      } finally {
        setVerifyLoading(false);
      }
    }

    async function resendOTP(){
      if (resendTimer > 0) return;
      const sid = document.body.dataset.sid || '';
      if (!sid){ showError('Doğrulama oturum bilgisi yok. Yeniden giriş yapın.'); return; }

      try{
        const res = await fetch(location.pathname, {
          method:'POST',
          headers:{'Content-Type':'application/json'},
          body: JSON.stringify({ resend: true, session_id: sid })
        });
        const data = await res.json().catch(()=>({}));
        if (!res.ok || !data?.success) throw new Error(data?.message || `HTTP ${res.status}`);

        showSuccess(data.message || 'Kod tekrar gönderildi.');
        document.getElementById('otpCode').value = '';
        document.getElementById('otpCode').focus();
        startResendTimer();
      }catch(e){ showError(e.message || 'Kod gönderilirken hata oluştu'); }
    }

    function backToLogin(){
      const href = document.body.dataset.loginHref || 'login.php';
      window.location.replace(href);
    }

    function startResendTimer(){
      resendTimer = 60;
      const btn = document.getElementById('resendBtn');
      const cd  = document.getElementById('countdown');
      btn.disabled = true;
      btn.classList.add('disabled:text-gray-400','disabled:cursor-not-allowed');
      resendInterval = setInterval(()=>{
        if(resendTimer>0){ cd.textContent = ` (${resendTimer}s)`; resendTimer--; }
        else { clearResendTimer(); }
      },1000);
    }
    function clearResendTimer(){
      if(resendInterval){ clearInterval(resendInterval); resendInterval=null; }
      resendTimer=0;
      const btn=document.getElementById('resendBtn');
      const cd=document.getElementById('countdown');
      btn.disabled=false;
      btn.classList.remove('disabled:text-gray-400','disabled:cursor-not-allowed');
      cd.textContent='';
    }
    function setVerifyLoading(loading){
      const btn=document.getElementById('verifyBtn');
      const txt=document.getElementById('verifyBtnText');
      const spn=document.getElementById('verifySpinner');
      if(loading){ btn.disabled=true; txt.textContent='Doğrulanıyor...'; spn.classList.remove('hidden'); }
      else { btn.disabled=false; txt.textContent='Doğrula'; spn.classList.add('hidden'); }
    }

    function showSuccess(msg){ showToast(msg,true); }
    function showError(msg){ showToast(msg,false); }
    function showToast(message, ok=true){
      const root=document.getElementById('toastRoot');
      const el=document.createElement('div');
      el.className=`px-4 py-2 rounded-lg shadow-lg text-white text-sm ${ok?'bg-green-600 success-message':'bg-red-600 error-message'}`;
      el.textContent=message;
      root.appendChild(el);
      setTimeout(()=>{ el.remove(); }, 3500);
    }
  </script>
</body>
</html>
