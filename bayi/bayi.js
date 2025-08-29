/* =======================
   Bayi Paneli - temiz JS
   ======================= */
(function () {
  /* -------- Genel kontroller -------- */
  function toggleMobileMenu(){
  const el = document.getElementById('mobile-menu');
  if (el) el.classList.toggle('hidden');
}
function toggleUserMenu() {
  const el = document.getElementById('user-menu');
  if (el) {
    el.classList.toggle('hidden');
  }
}

// Menü dışında bir yere tıklayınca kapanması için:
document.addEventListener('click', function(e){
  const menu = document.getElementById('user-menu');
  const btn  = e.target.closest('button[onclick="toggleUserMenu()"]');
  if (!menu || btn) return;
  if (!menu.contains(e.target)) {
    menu.classList.add('hidden');
  }
});
function logout(){
  window.location.href = '/is-ortaklar-paneli/auth/logout.php';
}

  /* -------- IFRAME tabanlı gezinme -------- */
  const BASE = '/is-ortaklar-paneli/bayi/';

  // Tek kaynak: tüm butonlar buradaki anahtarları kullanır
  const ROUTES = {
    dashboard:    'dashboard.php',
    registration: 'musteriKayit.php',
    customers:    'musterilerim.php',
    quotes:       'teklifler.php',
    orders:       'siparislerim.php',
    billing:      'faturalar.php',
  };

  const frameWrap    = document.getElementById('frame-wrap');
  const contentFrame = document.getElementById('content-frame');

  // Aktif sekme görünümü
  function setActiveNav(key) {
    // desktop
    document.querySelectorAll('.nav-btn').forEach(btn => {
      btn.classList.remove('text-indigo-600', 'border-b-2', 'border-indigo-500');
      btn.classList.add('text-gray-500', 'hover:text-gray-700');
    });
    const desk = document.querySelector(`.nav-btn[onclick="showSection('${key}')"]`);
    if (desk) {
      desk.classList.remove('text-gray-500', 'hover:text-gray-700');
      desk.classList.add('text-indigo-600', 'border-b-2', 'border-indigo-500');
    }
    // mobile
    document.querySelectorAll('.mobile-nav-btn').forEach(btn => {
      btn.classList.remove('text-indigo-600', 'bg-indigo-50');
      btn.classList.add('text-gray-700', 'hover:text-gray-900', 'hover:bg-gray-100');
    });
    const mob = document.querySelector(`.mobile-nav-btn[onclick*="showSection('${key}')"]`);
    if (mob) {
      mob.classList.remove('text-gray-700', 'hover:text-gray-900', 'hover:bg-gray-100');
      mob.classList.add('text-indigo-600', 'bg-indigo-50');
    }
  }

  // Navbar’dan çağrılacak tek fonksiyon
  function showSection(key) {
    const file = ROUTES[key] || ROUTES.dashboard;

    // iframe alanını görünür yap
    if (frameWrap) frameWrap.classList.remove('hidden');
    if (!contentFrame) return;

    // embed=1 ile yalın içerik
    const url = BASE + file + (file.includes('?') ? '&' : '?') + 'embed=1';
    if (contentFrame.getAttribute('src') !== url) {
      contentFrame.src = url;
    }

    setActiveNav(key);
    history.pushState({ key }, '', '#' + key);
  }

  // Geri/ileri
  window.addEventListener('popstate', (e) => {
    const key = (e.state && e.state.key) || 'dashboard';
    showSection(key);
  });

  // Çocuk sayfa yükseklik mesajı (her iki tip de kabul)
  window.addEventListener('message', (e) => {
    if (!contentFrame || e.source !== contentFrame.contentWindow) return;
    if (!e.data) return;
    if (e.data.type !== 'resize-iframe' && e.data.type !== 'musterikayit-height') return;
    const h = Math.max(480, Number(e.data.height || 0));
    contentFrame.style.height = h + 'px';
  });

  // Fallback: çocuk sayfa postMessage atmazsa yine de yükseklik ayarla
  if (contentFrame) {
    contentFrame.addEventListener('load', () => {
      setTimeout(() => {
        try {
          const doc = contentFrame.contentDocument || contentFrame.contentWindow.document;
          if (!doc) return;
          const h = Math.max(
            doc.body.scrollHeight, doc.body.offsetHeight,
            doc.documentElement.clientHeight, doc.documentElement.scrollHeight, doc.documentElement.offsetHeight
          );
          contentFrame.style.height = (h ? Math.max(480, h) : 600) + 'px';
        } catch (_) { /* cross-origin değilse sorun yok */ }
      }, 120);
    });
  }

  // İlk açılış: hash varsa ona, yoksa dashboard
  document.addEventListener('DOMContentLoaded', () => {
    const key = (location.hash || '#dashboard').slice(1);
    showSection(ROUTES[key] ? key : 'dashboard');
  });

  // Global export (onclick’ler için)
  window.toggleMobileMenu = toggleMobileMenu;
  window.toggleUserMenu   = toggleUserMenu;
  window.logout           = logout;
  window.showSection      = showSection;

  // Örnek aksiyonlar
  window.createNewQuote = () => showSection('quotes');
  window.viewQuote      = (id) => alert(`Teklif: ${id}`);
  window.viewOrder      = (id) => alert(`Sipariş: ${id}`);
})();
