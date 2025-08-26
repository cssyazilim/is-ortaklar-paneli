<?php
// ====== SAYFA BAŞI (HTML'DEN ÖNCE) ======
require_once __DIR__ . '/../config/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

/* API kökü: .../api/auth -> .../api */
if (!defined('API_ROOT')) {
  define('API_ROOT', preg_replace('~/auth/?$~i', '', rtrim(API_BASE, '/')));
}

/* Sadece Bearer ile GET (Postman'dakiyle aynı mantık) */
function http_get_json_bearer(string $base, string $path, string $token): array {
  $url = rtrim($base,'/').'/'.ltrim($path,'/');
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
      'Accept: application/json',
      'Authorization: Bearer ' . $token,
    ],
    CURLOPT_TIMEOUT        => 20,
  ]);
  $raw  = curl_exec($ch);
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $err  = curl_error($ch);
  curl_close($ch);

  if ($raw === false)         throw new RuntimeException('Sunucuya ulaşılamadı: '.$err);
  $json = json_decode($raw,true);
  if (!is_array($json))       throw new RuntimeException('Geçersiz yanıt: '.$raw);
  if ($code < 200 || $code >= 300) {
    $msg = $json['message'] ?? $json['error'] ?? ('HTTP '.$code);
    throw new RuntimeException($msg);
  }
  return $json;
}

/* --- TOKEN ---
   Buradan token’ı alın. Admin ya da partner token hangisini kullandıracaksanız buraya koyun.
   İsterseniz hızlı test için ?token=... ile override edebilirsiniz.
*/
$accessToken = $_GET['token'] ?? (
  $_SESSION['accessToken']            // daha önce kaydedilmiş genel token
  ?? ($_SESSION['admin_accessToken'] ?? $_SESSION['partner_accessToken'] ?? '')
);

$apiError    = null;
$customers   = [];
$totalCount  = 0;

if ($accessToken) {
  try {
    $resp       = http_get_json_bearer(API_ROOT, 'customers', $accessToken);
    $customers  = $resp['items'] ?? [];
    $totalCount = (int)($resp['total'] ?? 0);
  } catch (Throwable $e) {
    // Örn: "PartnerScopeRequired" hatası buradan gelir
    $apiError = $e->getMessage();
  }
} else {
  $apiError = 'Token bulunamadı (oturum açın).';
}

// JS tarafında kullanmak için veriyi gömelim
?>
<script>
// API sonucu geldi mi?
const apiErr = window.__API_ERROR__;
const customers = window.__CUSTOMERS__ || [];
const total = window.__CUSTOMERS_T__ || 0;

// Üstteki "Toplam X teklif" yazısını güncellemek istiyorsanız:
document.addEventListener('DOMContentLoaded', () => {
  const infoP = document.querySelector('p.text-sm.text-gray-600');
  if (infoP) infoP.textContent = `Toplam ${total} teklif`;

  if (apiErr) {
    // Liste kartında bir hata alanına basitçe yazdırın
    const container = document.getElementById('quotes-container');
    if (container) {
      container.innerHTML = `<div class="col-span-full p-4 text-red-600">API Hatası: ${apiErr}</div>`;
    }
    return;
  }

  // Customers -> “teklif kartı” verisine dönüştür (uydurma alan eşleme)
  const quotesData = customers.map((c, idx) => ({
    id: idx + 1,
    title: c.title || c.name || 'Teklif',
    customer: c.title || '-',
    contact: c.type || '-',           // elimizde gerçek kişi yoksa type'ı gösteriyoruz
    category: 'yazilim',              // backend sağlamıyorsa sabit/boş bırakın
    status: (c.status === 'active' ? 'onaylandi' : 'beklemede'),
    amount: '-',                      // backend’de yoksa '-'
    deadline: c.updated_at || c.created_at || '',
    createdDate: c.created_at || '',
    description: c.program ? `Program: ${c.program}` : '',
    products: []                      // backend sağlamıyorsa boş
  }));

  // Artık sizin mevcut render fonksiyonunuza veriyi verelim.
  window.filteredQuotes = quotesData;
  renderQuotes();
  setupSearch();
});
</script>



<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tekliflerim - Bayi Yönetim Sistemi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .card-shadow{box-shadow:0 4px 6px -1px rgba(0,0,0,.1),0 2px 4px -1px rgba(0,0,0,.06)}
        .success-message{animation:slideIn .3s ease-out}@keyframes slideIn{from{transform:translateY(-10px);opacity:0}to{transform:translateY(0);opacity:1}}
        .product-item{transition:all .2s ease}.product-item:hover{background:#F9FAFB}
        .file-upload-area{border:2px dashed #D1D5DB;transition:all .2s ease}.file-upload-area:hover{border-color:#3B82F6;background:#F8FAFC}.file-upload-area.dragover{border-color:#3B82F6;background:#EBF8FF}
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
<header class="bg-white shadow-sm border-b border-gray-200 px-4 md:px-6 py-4">
  <div class="flex items-center justify-between">
    <div class="flex items-center">
      <button onclick="goBack()" class="p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 mr-3">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
      </button>
      <h1 class="text-xl md:text-2xl font-bold text-gray-900">Tekliflerim</h1>
    </div>
    <div class="flex items-center space-x-4">
      <button onclick="createNewQuote()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
        Yeni Teklif
      </button>
    </div>
  </div>
</header>

<main class="max-w-7xl mx-auto p-4 md:p-6">
  <!-- İstatistik Kartları (sabit demo) -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-6">
    <div class="bg-white p-4 md:p-6 rounded-lg card-shadow">
      <div class="flex items-center">
        <div class="w-10 h-10 md:w-12 md:h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
          <svg class="w-5 h-5 md:w-6 md:h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="ml-3 md:ml-4">
          <p class="text-xs md:text-sm font-medium text-gray-600">Bekleyen Teklifler</p>
          <p class="text-xl md:text-2xl font-bold text-gray-900" id="pendingCount">0</p>
        </div>
      </div>
    </div>
    <div class="bg-white p-4 md:p-6 rounded-lg card-shadow">
      <div class="flex items-center">
        <div class="w-10 h-10 md:w-12 md:h-12 bg-green-100 rounded-lg flex items-center justify-center">
          <svg class="w-5 h-5 md:w-6 md:h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>
        <div class="ml-3 md:ml-4">
          <p class="text-xs md:text-sm font-medium text-gray-600">Onaylanan</p>
          <p class="text-xl md:text-2xl font-bold text-gray-900" id="approvedCount">0</p>
        </div>
      </div>
    </div>
    <div class="bg-white p-4 md:p-6 rounded-lg card-shadow">
      <div class="flex items-center">
        <div class="w-10 h-10 md:w-12 md:h-12 bg-red-100 rounded-lg flex items-center justify-center">
          <svg class="w-5 h-5 md:w-6 md:h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </div>
        <div class="ml-3 md:ml-4">
          <p class="text-xs md:text-sm font-medium text-gray-600">Reddedilen</p>
          <p class="text-xl md:text-2xl font-bold text-gray-900" id="rejectedCount">0</p>
        </div>
      </div>
    </div>
    <div class="bg-white p-4 md:p-6 rounded-lg card-shadow">
      <div class="flex items-center">
        <div class="w-10 h-10 md:w-12 md:h-12 bg-blue-100 rounded-lg flex items-center justify-center">
          <svg class="w-5 h-5 md:w-6 md:h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        </div>
        <div class="ml-3 md:ml-4">
          <p class="text-xs md:text-sm font-medium text-gray-600">Başarı Oranı</p>
          <p class="text-xl md:text-2xl font-bold text-gray-900" id="successRate">0%</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Filtreler -->
  <div class="bg-white rounded-lg card-shadow mb-6">
    <div class="p-4 md:p-6 border-b border-gray-200">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h2 class="text-lg font-semibold text-gray-900">Teklif Listesi</h2>
          <p class="text-sm text-gray-600 mt-1" id="totalText">Toplam 0 teklif</p>
          <?php if ($apiError): ?>
            <p class="text-sm text-red-600 mt-1">API Hatası: <?= htmlspecialchars($apiError, ENT_QUOTES, 'UTF-8') ?></p>
          <?php endif; ?>
        </div>
        <div class="flex flex-col md:flex-row gap-3">
          <div class="relative">
            <input type="text" id="searchQuotes" placeholder="Teklif ara..." class="w-full md:w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          </div>
          <select id="statusFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="filterQuotes()">
            <option value="">Tüm Durumlar</option>
            <option value="beklemede">Beklemede</option>
            <option value="onaylandi">Onaylandı</option>
            <option value="reddedildi">Reddedildi</option>
            <option value="suresi-doldu">Süresi Doldu</option>
          </select>
          <select id="categoryFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="filterQuotes()">
            <option value="">Tüm Kategoriler</option>
          </select>
        </div>
      </div>
    </div>
  </div>

  <!-- Kartlar -->
  <div id="quotes-container" class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4 md:gap-6 mb-6"></div>

  <!-- Detay Modal -->
  <div id="quote-detail-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
      <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900">Teklif Detayları</h3>
          <button onclick="closeQuoteDetail()" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>
      </div>
      <div id="quote-detail-content" class="p-6"></div>
    </div>
  </div>
</main>

<script>
  // PHP'den gelen veriler
  const quotesData = <?php echo json_encode($quotes, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); ?>;
  let filteredQuotes = [...quotesData];

  window.addEventListener('load', () => {
    setupSearch();
    filterQuotes();
    updateStats();
  });

  function updateStats(){
    const total = quotesData.length;
    const pending = quotesData.filter(q => q.status === 'beklemede').length;
    const approved= quotesData.filter(q => q.status === 'onaylandi').length;
    const rejected= quotesData.filter(q => q.status === 'reddedildi').length;

    document.getElementById('totalText').textContent   = `Toplam ${total} teklif`;
    document.getElementById('pendingCount').textContent = pending;
    document.getElementById('approvedCount').textContent= approved;
    document.getElementById('rejectedCount').textContent= rejected;
    const successRate = total ? Math.round((approved/total)*100) : 0;
    document.getElementById('successRate').textContent = successRate + '%';
  }

  function renderQuotes() {
    const container = document.getElementById('quotes-container');
    container.innerHTML = '';

    if (!filteredQuotes.length) {
      container.innerHTML = `
        <div class="col-span-full text-center py-12">
          <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
          <p class="text-gray-500 text-lg">Aradığınız kriterlere uygun teklif bulunamadı</p>
        </div>`;
      return;
    }

    filteredQuotes.forEach(q => container.appendChild(createQuoteCard(q)));
  }

  function createQuoteCard(quote) {
    const card = document.createElement('div');
    card.className = 'bg-white rounded-lg card-shadow hover:shadow-lg transition-shadow';

    const statusColors = {
      'beklemede': 'bg-yellow-100 text-yellow-800',
      'onaylandi': 'bg-green-100 text-green-800',
      'reddedildi': 'bg-red-100 text-red-800',
      'suresi-doldu': 'bg-gray-100 text-gray-800'
    };
    const statusTexts = {
      'beklemede': 'Beklemede',
      'onaylandi': 'Onaylandı',
      'reddedildi': 'Reddedildi',
      'suresi-doldu': 'Süresi Doldu'
    };

    card.innerHTML = `
      <div class="p-6">
        <div class="flex items-start justify-between mb-4">
          <div class="flex-1">
            <h3 class="text-lg font-semibold text-gray-900 mb-1">${escapeHtml(quote.title || '-')}</h3>
            <p class="text-sm text-gray-600">${escapeHtml(quote.customer || '-')}</p>
            ${quote.contact ? `<p class="text-xs text-gray-500">${escapeHtml(quote.contact)}</p>` : ''}
          </div>
          <span class="px-2 py-1 text-xs font-medium rounded-full ${statusColors[quote.status] || 'bg-gray-100 text-gray-800'}">
            ${statusTexts[quote.status] || 'Beklemede'}
          </span>
        </div>

        <div class="space-y-3 mb-4">
          <div class="flex justify-between items-center">
            <span class="text-sm text-gray-600">Tutar:</span>
            <span class="font-semibold text-gray-900">${quote.amount != null ? ('₺' + Number(quote.amount).toLocaleString('tr-TR')) : '-'}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-sm text-gray-600">Son Tarih:</span>
            <span class="text-sm text-gray-900">${formatDate(quote.deadline)}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-sm text-gray-600">Oluşturma:</span>
            <span class="text-sm text-gray-900">${formatDate(quote.createdDate)}</span>
          </div>
        </div>

        <div class="border-t border-gray-200 pt-4">
          <div class="flex space-x-2">
            <button onclick="viewQuoteDetail('${quote.id}')" class="flex-1 px-3 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">Detay Görüntüle</button>
            <button onclick="editQuote('${quote.id}')" class="flex-1 px-3 py-2 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">Düzenle</button>
          </div>
        </div>
      </div>`;
    return card;
  }

  function setupSearch() {
    const searchInput = document.getElementById('searchQuotes');
    searchInput.addEventListener('input', (e) => filterQuotes(e.target.value.toLowerCase()));
  }

  function filterQuotes(searchTerm = '') {
    const statusFilter = document.getElementById('statusFilter').value;
    const categoryFilter = document.getElementById('categoryFilter').value;

    filteredQuotes = quotesData.filter(q => {
      const matchesSearch =
        !searchTerm ||
        (q.title||'').toLowerCase().includes(searchTerm) ||
        (q.customer||'').toLowerCase().includes(searchTerm) ||
        (q.contact||'').toLowerCase().includes(searchTerm);

      const matchesStatus = !statusFilter || q.status === statusFilter;
      const matchesCategory = !categoryFilter || (q.category||'') === categoryFilter;

      return matchesSearch && matchesStatus && matchesCategory;
    });

    renderQuotes();
    updateStats();
  }

  function viewQuoteDetail(id) {
    const quote = quotesData.find(q => q.id === id);
    if (!quote) return;
    const modal = document.getElementById('quote-detail-modal');
    const content = document.getElementById('quote-detail-content');

    content.innerHTML = `
      <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <h4 class="text-lg font-semibold text-gray-900 mb-4">Proje Bilgileri</h4>
            <div class="space-y-3">
              <div><span class="text-sm font-medium text-gray-600">Proje Adı:</span><p class="text-gray-900">${escapeHtml(quote.title||'-')}</p></div>
              ${quote.description ? `<div><span class="text-sm font-medium text-gray-600">Açıklama:</span><p class="text-gray-900">${escapeHtml(quote.description)}</p></div>` : ''}
              ${quote.category ? `<div><span class="text-sm font-medium text-gray-600">Kategori:</span><p class="text-gray-900">${escapeHtml(quote.category)}</p></div>` : ''}
            </div>
          </div>
          <div>
            <h4 class="text-lg font-semibold text-gray-900 mb-4">Müşteri Bilgileri</h4>
            <div class="space-y-3">
              <div><span class="text-sm font-medium text-gray-600">Şirket:</span><p class="text-gray-900">${escapeHtml(quote.customer||'-')}</p></div>
              ${quote.contact ? `<div><span class="text-sm font-medium text-gray-600">İletişim Kişisi:</span><p class="text-gray-900">${escapeHtml(quote.contact)}</p></div>` : ''}
              <div><span class="text-sm font-medium text-gray-600">Toplam Tutar:</span><p class="text-xl font-bold text-gray-900">${quote.amount != null ? ('₺' + Number(quote.amount).toLocaleString('tr-TR')) : '-'}</p></div>
            </div>
          </div>
        </div>

        <div>
          <h4 class="text-lg font-semibold text-gray-900 mb-4">Önemli Tarihler</h4>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-4 bg-gray-50 rounded-lg"><span class="text-sm font-medium text-gray-600">Oluşturma Tarihi:</span><p class="text-gray-900">${formatDate(quote.createdDate)}</p></div>
            <div class="p-4 bg-gray-50 rounded-lg"><span class="text-sm font-medium text-gray-600">Son Teslim Tarihi:</span><p class="text-gray-900">${formatDate(quote.deadline)}</p></div>
          </div>
        </div>

        <div class="flex flex-col md:flex-row gap-3 pt-6 border-t border-gray-200">
          <button onclick="editQuote('${quote.id}')" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">Teklifi Düzenle</button>
          <button onclick="downloadQuotePDF('${quote.id}')" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">PDF İndir</button>
        </div>
      </div>`;
    modal.classList.remove('hidden');
  }

  function closeQuoteDetail(){ document.getElementById('quote-detail-modal').classList.add('hidden'); }

  // Helpers
  function formatDate(s){
    if(!s) return '-';
    const d = new Date(s);
    if (isNaN(d)) return '-';
    return d.toLocaleDateString('tr-TR',{day:'2-digit',month:'short',year:'numeric'});
  }
  function escapeHtml(str){ return (str||'').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }

  // Actions (stub)
  function createNewQuote(){ alert('Yeni teklif oluşturma sayfasına gidilecek.'); }
  function editQuote(id){ alert('Teklif düzenleme: '+id); }
  function downloadQuotePDF(id){ alert('PDF indir: '+id); }
  function goBack(){ history.back(); }
</script>
</body>
</html>
