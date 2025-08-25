// Mobil menü aç/kapat
function toggleMobileMenu() {
  const mobileMenu = document.getElementById('mobile-menu');
  if (mobileMenu) mobileMenu.classList.toggle('hidden');
}

// Kullanıcı menüsü aç/kapat
function toggleUserMenu() {
  const userMenu = document.getElementById('user-menu');
  if (userMenu) userMenu.classList.toggle('hidden');
}

// Çıkış yap
function logout() {
  window.location.href = '/is-ortaklar-paneli/auth/logout.php';
}

// Dışarı tıklanınca user menüyü kapat
document.addEventListener('click', function (event) {
  const userMenu = document.getElementById('user-menu');
  const button = event.target.closest('button[onclick="toggleUserMenu()"]');
  if (!userMenu) return;
  if (!button && !userMenu.contains(event.target)) {
    userMenu.classList.add('hidden');
  }
});

// Sayfa sekmeleri arasında geçiş
function showSection(sectionName) {
  // tüm section’ları gizle
  document.querySelectorAll('.section').forEach(s => s.classList.add('hidden'));

  // seçilen section’ı göster
  const target = document.getElementById(sectionName);
  if (target) target.classList.remove('hidden');

  // Desktop nav güncelle
  document.querySelectorAll('.nav-btn').forEach(btn => {
    btn.classList.remove('text-indigo-600', 'border-b-2', 'border-indigo-500');
    btn.classList.add('text-gray-500', 'hover:text-gray-700');
  });
  const activeDesktopBtn = document.querySelector(`.nav-btn[onclick="showSection('${sectionName}')"]`);
  if (activeDesktopBtn) {
    activeDesktopBtn.classList.remove('text-gray-500', 'hover:text-gray-700');
    activeDesktopBtn.classList.add('text-indigo-600', 'border-b-2', 'border-indigo-500');
  }

  // Mobil nav güncelle
  document.querySelectorAll('.mobile-nav-btn').forEach(btn => {
    btn.classList.remove('text-indigo-600', 'bg-indigo-50');
    btn.classList.add('text-gray-700', 'hover:text-gray-900', 'hover:bg-gray-100');
  });
  const activeMobileBtn = document.querySelector(`.mobile-nav-btn[onclick*="showSection('${sectionName}')"]`);
  if (activeMobileBtn) {
    activeMobileBtn.classList.remove('text-gray-700', 'hover:text-gray-900', 'hover:bg-gray-100');
    activeMobileBtn.classList.add('text-indigo-600', 'bg-indigo-50');
  }
}

// Teklif aksiyonları
function createNewQuote() {
  alert('Yeni teklif oluşturma sayfasına yönlendiriliyorsunuz...');
}
function viewQuote(quoteId) {
  alert(`${quoteId} numaralı teklif detayları görüntüleniyor...`);
}

// Sipariş aksiyonları
function viewOrder(orderId) {
  alert(`${orderId} numaralı sipariş detayları görüntüleniyor...`);
}

// Otomatik faturalandırma simülasyonu
function simulateAutoBilling() {
  console.log('Otomatik faturalandırma çalışıyor...');
}

// Sayfa yüklenince
document.addEventListener('DOMContentLoaded', function () {
  setInterval(simulateAutoBilling, 60000);
  showSection('dashboard'); // açılışta dashboard
});

// Fonksiyonlar globalde 
window.toggleMobileMenu = toggleMobileMenu;
window.toggleUserMenu   = toggleUserMenu;
window.logout           = logout;
window.showSection      = showSection;
window.createNewQuote   = createNewQuote;
window.viewQuote        = viewQuote;
window.viewOrder        = viewOrder;
