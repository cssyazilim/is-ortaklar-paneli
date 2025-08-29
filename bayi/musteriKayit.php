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
          <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Müşteri Kayıt Süreci</h2>
          <p class="text-sm sm:text-base text-gray-600">Yeni müşteri kayıt işlemleri ve onay durumu</p>
        </div>

        <!-- Süreç adımları -->
        <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6 mb-4 sm:mb-6">
          <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-4">Kayıt Süreci Adımları</h3>
          <div class="flex items-center justify-between overflow-x-auto pb-2">
            <div class="flex flex-col items-center flex-shrink-0">
              <div class="w-8 h-8 sm:w-10 sm:h-10 bg-green-500 rounded-full flex items-center justify-center text-white font-semibold text-sm sm:text-base">1</div>
              <p class="text-xs sm:text-sm mt-2 text-center whitespace-nowrap">Form<br class="hidden sm:block">Doldurma</p>
            </div>
            <div class="flex-1 h-1 bg-green-500 mx-1 sm:mx-2 min-w-[20px]"></div>
            <div class="flex flex-col items-center flex-shrink-0">
              <div class="w-8 h-8 sm:w-10 sm:h-10 bg-green-500 rounded-full flex items-center justify-center text-white font-semibold text-sm sm:text-base">2</div>
              <p class="text-xs sm:text-sm mt-2 text-center whitespace-nowrap">Operasyon<br class="hidden sm:block">İnceleme</p>
            </div>
            <div class="flex-1 h-1 bg-yellow-400 mx-1 sm:mx-2 min-w-[20px]"></div>
            <div class="flex flex-col items-center flex-shrink-0">
              <div class="w-8 h-8 sm:w-10 sm:h-10 bg-yellow-400 rounded-full flex items-center justify-center text-white font-semibold text-sm sm:text-base">3</div>
              <p class="text-xs sm:text-sm mt-2 text-center whitespace-nowrap">Ek Evrak<br class="hidden sm:block">Bekleniyor</p>
            </div>
            <div class="flex-1 h-1 bg-gray-300 mx-1 sm:mx-2 min-w-[20px]"></div>
            <div class="flex flex-col items-center flex-shrink-0">
              <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gray-300 rounded-full flex items-center justify-center text-gray-600 font-semibold text-sm sm:text-base">4</div>
              <p class="text-xs sm:text-sm mt-2 text-center whitespace-nowrap">Onay</p>
            </div>
            <div class="flex-1 h-1 bg-gray-300 mx-1 sm:mx-2 min-w-[20px]"></div>
            <div class="flex flex-col items-center flex-shrink-0">
              <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gray-300 rounded-full flex items-center justify-center text-gray-600 font-semibold text-sm sm:text-base">5</div>
              <p class="text-xs sm:text-sm mt-2 text-center whitespace-nowrap">Giriş<br class="hidden sm:block">Aktif</p>
            </div>
          </div>
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
    // Form örnek submit (API bağlamadıysanız simülasyon)
    document.getElementById('kayitForm')?.addEventListener('submit', function(e){
      e.preventDefault();
      // TODO: Burada gerçek API'ye POST edebilirsin.
      document.getElementById('formMsg').classList.remove('hidden');
      setTimeout(()=>document.getElementById('formMsg').classList.add('hidden'), 3000);
    });

    // Parent yüksekliğine haber ver
    function postHeight(){
      try{
        const h = Math.max(
          document.body.scrollHeight,
          document.documentElement.scrollHeight
        );
      parent.postMessage({ type: 'resize-iframe', height: h }, '*');

      }catch(_){}
    }
    new ResizeObserver(postHeight).observe(document.body);
    window.addEventListener('load', postHeight);
  </script>
</body>
</html>
