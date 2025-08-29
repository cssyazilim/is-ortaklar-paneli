<?php
// file: /is-ortaklar-paneli/bayi/musteriKayit.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'bayi') {
  header('Location: /is-ortaklar-paneli/login.php');
  exit;
}

/* Embed kontrolü (iframe için) */
$EMBED = (
  isset($_GET['embed']) ||
  isset($_SERVER['HTTP_X_EMBED']) ||
  (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])==='xmlhttprequest')
);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Müşteri Kayıt</title>
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Backend için meta (token/endpoint) -->
<meta name="partner-token" content="<?= htmlspecialchars($_SESSION['partnerAccessToken'] ?? $_SESSION['bayiAccessToken'] ?? $_SESSION['accessToken'] ?? '', ENT_QUOTES) ?>">

  <meta name="partners-me-url" content="/is-ortaklar-paneli/api/partners_me.php">

  <?php if(!$EMBED): ?>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/bayi.css">
  <?php endif; ?>
  <style>
    .card-shadow{ box-shadow:0 4px 6px -1px rgba(0,0,0,.08), 0 2px 4px -1px rgba(0,0,0,.04); }
  </style>
</head>
<body class="<?php echo $EMBED ? 'bg-transparent' : 'bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen'; ?>">

  <div class="<?php echo $EMBED ? '' : 'max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8'; ?>">
    <!-- Tek beyaz kart -->
    <section class="rounded-2xl bg-white ring-1 ring-gray-100 card-shadow overflow-hidden">
      <div class="p-4 sm:p-6 lg:p-8">
        <div class="mb-4 sm:mb-8">
          <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Müşteri Kayıt ve Onay Sürecim</h2>
          <p class="text-sm sm:text-base text-gray-600">Yeni müşteri kayıt işlemleri ve onay durumum</p>
        </div>

        <!-- Süreç adımları (dinamik) -->
        <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6 mb-4 sm:mb-6">
          <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-4">Kayıt Süreci Adımlarım</h3>
          <div id="workflow-stepper" class="overflow-x-auto pb-2"></div>
        </div>

        <!-- Kayıt formu -->
        <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
          <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-4">Yeni Müşteri Kayıt Formu</h3>

          <form id="kayitForm" class="space-y-3 sm:space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
              <div>
                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Şirket Adı</label>
                <input name="company" type="text" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="ABC Teknoloji Ltd." required>
              </div>
              <div>
                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Vergi Numarası</label>
                <input name="tax_no" type="text" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="1234567890" required>
              </div>
              <div>
                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Yetkili Kişi</label>
                <input name="contact" type="text" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Ahmet Yılmaz" required>
              </div>
              <div>
                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Telefon</label>
                <input name="phone" type="tel" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="0532 123 45 67">
              </div>
              <div>
                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">E-posta</label>
                <input name="email" type="email" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="info@abcteknoloji.com">
              </div>
              <div>
                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Şehir</label>
                <select name="city" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                  <option>İstanbul</option><option>Ankara</option><option>İzmir</option><option>Bursa</option>
                </select>
              </div>
            </div>

            <div>
              <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Adres</label>
              <textarea name="address" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" rows="3" placeholder="Tam adres bilgisi..."></textarea>
            </div>

            <div class="flex justify-end pt-2">
              <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 sm:px-6 py-2 rounded-lg text-sm font-medium transition-colors">
                Kayıt Başvurusu Gönder
              </button>
            </div>
          </form>

          <p id="formMsg" class="mt-3 text-sm text-green-600 hidden">Başvurunuz alındı.</p>
        </div>
      </div>
    </section>
  </div>

<script>
// ---- NET eşleştirme: sadece backend status alanına bak
function resolveStage(payload) {
  const status = String(payload?.status || '').toLowerCase();

  if (status === 'active')   return { stage: 5, errorAt: null }; // giriş aktif => 5 yeşil
  if (status === 'pending')  return { stage: 2, errorAt: null }; // inceleme => 2 sarı
  if (status === 'inactive') return { stage: 4, errorAt: 4 };    // onaylanmadı => 4 kırmızı

  // bilinmeyen durumda güvenli varsayılan
  return { stage: 2, errorAt: null };
}

// Stepper: errorAt === 4 ise 4. adım kırmızı; stage===5 ise 5 tamamen yeşil
function renderStepper(stage = 2, errorAt = null) {
  const steps = [
    { n: 1, label: 'Form Doldurma' },
    { n: 2, label: 'Operasyon İnceleme' },
    { n: 3, label: 'Ek Evrak Bekleniyor' },
    { n: 4, label: 'Onay' },
    { n: 5, label: 'Giriş Aktif' },
  ];

  const stateOf = (i) => {
    if (i === errorAt) return 'error';
    if (i < stage)      return 'done';
    if (i === stage)    return (stage === 5 ? 'done' : 'current');
    return 'todo';
  };

  const circleCls = (st) =>
      st === 'error'   ? 'bg-red-500 text-white'
    : st === 'done'    ? 'bg-green-500 text-white'
    : st === 'current' ? 'bg-amber-400 text-white'
    :                    'bg-gray-200 text-gray-600';

  const segCls = (st) =>
      st === 'error'   ? 'bg-red-500'
    : st === 'done'    ? 'bg-green-500'
    : st === 'current' ? 'bg-amber-400'
    :                    'bg-gray-300';

  let html = '<div class="flex items-center">';
  steps.forEach((s, idx) => {
    const st = stateOf(s.n);
    html += `
      <div class="flex flex-col items-center">
        <div class="w-10 h-10 ${circleCls(st)} rounded-full flex items-center justify-center font-bold">${s.n}</div>
        <div class="mt-2 text-sm text-gray-700 text-center leading-5">
          ${s.label.replace(' ', '<br>')}
        </div>
      </div>
    `;
    if (idx < steps.length - 1) {
      const nextState = stateOf(s.n + 1);
      html += `<div class="flex-1 h-1 mx-6 ${segCls(nextState)} rounded-full"></div>`;
    }
  });
  html += '</div>';
  document.getElementById('workflow-stepper').innerHTML = html;
}

// Backend’ten çek ve uygula (sadece status kullan)
async function loadWorkflow() {
  try {
    const tokenMeta = document.querySelector('meta[name="partner-token"]');
    const urlMeta   = document.querySelector('meta[name="partners-me-url"]');
    const token = (tokenMeta?.content || '').trim();
    const url   = (urlMeta?.content   || '/is-ortaklar-paneli/api/partners_me.php').trim();

    const res = await fetch(url, {
      headers: {
        'Accept': 'application/json',
        ...(token ? { 'Authorization': token.startsWith('Bearer ') ? token : 'Bearer ' + token } : {})
      }
    });
    const data = await res.json();
    const payload = data?.partner || data;

    const { stage, errorAt } = resolveStage(payload);
    renderStepper(stage, errorAt);
  } catch {
    renderStepper(2, null);
  }
}

loadWorkflow();
</script>



</body>
</html>
